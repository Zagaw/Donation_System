<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Matches; // Add this line
use App\Models\Interest;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    // CREATE DONATION
    public function store(Request $request)
    {
        $request->validate([
            'itemName' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',

             // optional fields
            'nrcNumber' => 'nullable|string',
            'nrcFrontImage' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'nrcBackImage' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        $donor = $request->user()->donor;

        // Upload images if exist
        $frontPath = null;
        $backPath = null;

        if ($request->hasFile('nrcFrontImage')) {
            $frontPath = $request->file('nrcFrontImage')->store('nrc', 'public');
        }

        if ($request->hasFile('nrcBackImage')) {
            $backPath = $request->file('nrcBackImage')->store('nrc', 'public');
        }

        $donation = Donation::create([
            'donorId' => $donor->id,
            'category' => $request->category,
            'itemName' => $request->itemName,
            'quantity' => $request->quantity,
            'description' => $request->description,
            'status' => 'pending',
            'nrcNumber' => $request->nrcNumber,
            'nrcFrontImage' => $frontPath,
            'nrcBackImage' => $backPath
        ]);

        return response()->json([
            'message' => 'Donation created successfully',
            'donation' => $donation
        ], 201);
    }

    // VIEW MY DONATIONS
    public function myDonations(Request $request)
    {
        $donor = $request->user()->donor;

        return Donation::where('donorId', $donor->id)->orderBy('created_at', 'desc')->get();
    }

    // New method for detailed view (returns wrapped object)
    public function myDonationsWithDetails(Request $request)
    {
        $donor = $request->user()->donor;
        $donations = Donation::where('donorId', $donor->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'donations' => $donations // Wrapped in object
        ]);
    }

    // DELETE DONATION
    public function destroy(Request $request, $id)
    {
        $donor = $request->user()->donor;

        $donation = Donation::where('donationId', $id)
            ->where('donorId', $donor->id)
            ->firstOrFail();

        $donation->delete();

        return response()->json([
            'message' => 'Donation deleted successfully'
        ]);
    }

    // VIEW MY MATCHES (as donor) - FIXED VERSION
    public function myMatches(Request $request)
    {
        try {
            $donor = $request->user()->donor;
            
            if (!$donor) {
                return response()->json([
                    'message' => 'Donor profile not found'
                ], 404);
            }

            // Get donation IDs for this donor
            $donationIds = Donation::where('donorId', $donor->id)
                ->pluck('donationId')
                ->toArray();

            // Get interest IDs for this donor
            $interestIds = Interest::where('donorId', $donor->id)
                ->pluck('interestId')
                ->toArray();

            $matches = Matches::with([
                'donation',
                'request.receiver.user',
                'interest'
            ])
            ->where(function($query) use ($donationIds, $interestIds) {
                $query->whereIn('donationId', $donationIds)
                    ->orWhereIn('interestId', $interestIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'matches' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching matches',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    // VIEW SINGLE MATCH DETAILS FOR DONOR
    public function getDonorMatchDetails($id)
    {
        try {
            $donor = auth()->user()->donor;
            
            if (!$donor) {
                return response()->json([
                    'message' => 'Donor profile not found'
                ], 404);
            }

            $match = Matches::with([
                'donation',
                'request.receiver.user',
                'interest'
            ])
            ->where('matchId', $id)
            ->where(function($query) use ($donor) {
                $query->whereHas('donation', function($q) use ($donor) {
                    $q->where('donorId', $donor->id);
                })->orWhereHas('interest', function($q) use ($donor) {
                    $q->where('donorId', $donor->id);
                });
            })
            ->first();

            if (!$match) {
                return response()->json([
                    'message' => 'Match not found'
                ], 404);
            }

            return response()->json([
                'match' => $match
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching match details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Donor requests to mark match as executed
 */
    public function requestExecution($matchId)
    {
        try {
            $donor = auth()->user()->donor;
            
            $match = Matches::where('matchId', $matchId)
                ->where(function($query) use ($donor) {
                    $query->whereHas('donation', function($q) use ($donor) {
                        $q->where('donorId', $donor->id);
                    })->orWhereHas('interest', function($q) use ($donor) {
                        $q->where('donorId', $donor->id);
                    });
                })
                ->first();

            if (!$match) {
                return response()->json([
                    'message' => 'Match not found'
                ], 404);
            }

            if ($match->status !== 'approved') {
                return response()->json([
                    'message' => 'Only approved matches can be requested for execution'
                ], 400);
            }

            // Set the execution requested flag
            $match->execution_requested = true;
            $match->execution_requested_by = 'donor';
            $match->execution_requested_at = now();
            $match->save();

            return response()->json([
                'message' => 'Execution request sent to admin successfully',
                'match' => $match
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error sending request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
