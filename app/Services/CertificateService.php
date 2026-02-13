<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Donation;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CertificateService
{
    /**
     * Generate a new certificate for a donation
     */
    public function generateCertificate(Donation $donation)
    {
        try {
            DB::beginTransaction();

            // Check if certificate already exists
            if ($donation->certificate) {
                DB::rollBack();
                return $donation->certificate;
            }

            // Get donor/user information
            $donor = $donation->donor;
            $user = auth()->user();

            // Create certificate record
            $certificate = Certificate::create([
                'user_id' => $donation->donorId ?? $user->id ?? 1,
                'donation_id' => $donation->donationId,
                'certificate_number' => $this->generateCertificateNumber(),
                'recipient_name' => $donor->name ?? $user->name ?? 'Valued Donor',
                'item_name' => $donation->itemName,
                'quantity' => $donation->quantity,
                'category' => $donation->category,
                'donor_name' => $donor->name ?? $user->name ?? null,
                'issue_date' => now(),
                'status' => 'generated'
            ]);

            // Generate PDF
            $this->generatePDF($certificate);

            DB::commit();
            
            return $certificate;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate PDF file for certificate
     */
    public function generatePDF(Certificate $certificate)
    {
        // Prepare HTML view
        $html = view('certificates.template', [
            'certificate' => $certificate,
            'issue_date' => $certificate->issue_date->format('F d, Y')
        ])->render();

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $options->set('isJavascriptEnabled', false);
        
        // Generate PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Create certificates directory if it doesn't exist
        if (!Storage::disk('public')->exists('certificates')) {
            Storage::disk('public')->makeDirectory('certificates');
        }

        // Save PDF file
        $filename = 'certificate_' . $certificate->certificate_number . '.pdf';
        $path = 'certificates/' . $filename;
        
        Storage::disk('public')->put($path, $dompdf->output());
        
        // Update certificate with file path
        $certificate->update([
            'file_path' => $path,
            'status' => 'generated'
        ]);
        
        return $path;
    }

    /**
     * Download certificate PDF
     */
    public function downloadCertificate(Certificate $certificate)
    {
        // Check if file exists, if not regenerate
        if (!$certificate->file_path || !Storage::disk('public')->exists($certificate->file_path)) {
            $this->generatePDF($certificate);
        }
        
        // Download file
        return Storage::disk('public')->download(
            $certificate->file_path,
            'Donation_Certificate_' . $certificate->certificate_number . '.pdf'
        );
    }

    /**
     * Regenerate certificate PDF
     */
    public function regenerateCertificate(Certificate $certificate)
    {
        // Delete old file if exists
        if ($certificate->file_path && Storage::disk('public')->exists($certificate->file_path)) {
            Storage::disk('public')->delete($certificate->file_path);
        }
        
        // Generate new PDF
        return $this->generatePDF($certificate);
    }

    /**
     * Revoke certificate
     */
    public function revokeCertificate(Certificate $certificate)
    {
        // Delete PDF file
        if ($certificate->file_path && Storage::disk('public')->exists($certificate->file_path)) {
            Storage::disk('public')->delete($certificate->file_path);
        }
        
        // Update certificate status
        $certificate->update([
            'status' => 'revoked',
            'file_path' => null
        ]);
        
        return $certificate;
    }

    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber()
    {
        $prefix = 'DON-';
        $year = date('Y');
        $month = date('m');
        $random = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        
        return $prefix . $year . $month . '-' . $random;
    }

    /**
     * Get certificate by donation
     */
    public function getCertificateByDonation(Donation $donation)
    {
        return Certificate::where('donation_id', $donation->donationId)->first();
    }
}