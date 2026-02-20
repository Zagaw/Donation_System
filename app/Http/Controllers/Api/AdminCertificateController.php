<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Matches;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCertificateController extends Controller
{
    /**
     * Get all completed matches that don't have certificates yet
     */
    public function getEligibleMatches()
    {
        try {
            $matches = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])
            ->where('status', 'completed')
            ->doesntHave('certificate') // This uses the relationship we just added
            ->orderBy('updated_at', 'desc')
            ->get();

            return response()->json([
                'matches' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching eligible matches',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Get all certificates (for admin management)
     */
    public function getAllCertificates()
    {
        try {
            $certificates = Certificate::with(['donor.user', 'match'])
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
     * Generate certificate for a completed match
     */
    public function generateCertificate(Request $request)
    {
        try {
            $request->validate([
                'matchId' => 'required|exists:matches,matchId',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string'
            ]);

            $match = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])->findOrFail($request->matchId);

            // Check if certificate already exists using relationship
            if ($match->certificate) {
                return response()->json([
                    'message' => 'Certificate already exists for this match'
                ], 400);
            }

            // Determine donor and receiver
            $donor = $match->donation?->donor ?? $match->interest?->donor;
            $receiver = $match->request?->receiver;

            if (!$donor || !$receiver) {
                return response()->json([
                    'message' => 'Could not determine donor or receiver'
                ], 400);
            }

            // Get item details
            $itemName = $match->donation?->itemName ?? $match->request?->itemName ?? 'Donation';
            $quantity = $match->donation?->quantity ?? $match->request?->quantity ?? 1;
            $category = $match->donation?->category ?? $match->request?->category ?? 'General';

            // Generate certificate
            $certificate = Certificate::create([
                'matchId' => $match->matchId,
                'donorId' => $donor->id,
                'certificateNumber' => Certificate::generateCertificateNumber(),
                'title' => $request->title,
                'description' => $request->description,
                'itemName' => $itemName,
                'quantity' => $quantity,
                'category' => $category,
                'recipientName' => $receiver->user->name,
                'issueDate' => now(),
                'status' => 'generated'
            ]);

            // TODO: Generate PDF (you'll need to install barryvdh/laravel-dompdf)
            // $pdf = Pdf::loadView('pdfs.certificate', ['certificate' => $certificate]);
            // $pdfPath = 'certificates/' . $certificate->certificateNumber . '.pdf';
            // Storage::disk('public')->put($pdfPath, $pdf->output());
            // $certificate->filePath = $pdfPath;
            // $certificate->save();

            return response()->json([
                'message' => 'Certificate generated successfully',
                'certificate' => $certificate->load(['donor.user', 'match'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating certificate',
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
            $certificate = Certificate::findOrFail($id);
            
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

    /**
     * Delete certificate
     */
    public function deleteCertificate($id)
    {
        try {
            $certificate = Certificate::findOrFail($id);
            
            // Delete file if exists
            if ($certificate->filePath) {
                Storage::disk('public')->delete($certificate->filePath);
            }
            
            $certificate->delete();

            return response()->json([
                'message' => 'Certificate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}