<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DonorCertificateController extends Controller
{
    /**
     * Get all certificates for the authenticated donor
     */
    public function myCertificates()
    {
        try {
            $donor = auth()->user()->donor;
            
            if (!$donor) {
                return response()->json([
                    'message' => 'Donor profile not found'
                ], 404);
            }

            $certificates = Certificate::with(['match'])
                ->where('donorId', $donor->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'certificates' => $certificates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single certificate details
     */
    public function getCertificate($id)
    {
        try {
            $donor = auth()->user()->donor;
            
            if (!$donor) {
                return response()->json([
                    'message' => 'Donor profile not found'
                ], 404);
            }

            $certificate = Certificate::with(['match.request.receiver.user', 'match.donation.donor.user'])
                ->where('donorId', $donor->id)
                ->where('certificateId', $id)
                ->firstOrFail();

            // Mark as viewed if status is 'sent'
            if ($certificate->status === 'sent') {
                $certificate->status = 'viewed';
                $certificate->save();
            }

            return response()->json([
                'certificate' => $certificate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download certificate PDF
     */
    public function downloadCertificate($id)
    {
        try {
            $donor = auth()->user()->donor;
            
            if (!$donor) {
                return response()->json([
                    'message' => 'Donor profile not found'
                ], 404);
            }

            $certificate = Certificate::where('donorId', $donor->id)
                ->where('certificateId', $id)
                ->firstOrFail();

            if (!$certificate->filePath) {
                return response()->json([
                    'message' => 'Certificate file not found'
                ], 404);
            }

            return Storage::disk('public')->download($certificate->filePath);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error downloading certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}