<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Request as ReceiverRequest;
use App\Models\Interest;
use App\Models\Matches;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\MatchCreatedNotification;

class AdminMatchController extends Controller
{
    // ================================
    // GET ALL MATCHES
    // ================================
    public function getAllMatches()
    {
        try {
            $matches = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'matches' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // GET APPROVED MATCHES
    // ================================
    public function getApprovedMatches()
    {
        try {
            $matches = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'matches' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching approved matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // GET EXECUTED MATCHES
    // ================================
    public function getExecutedMatches()
    {
        try {
            $matches = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])
            ->where('status', 'executed')
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'matches' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching executed matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // GET COMPLETED MATCHES
    // ================================
    public function getCompletedMatches()
    {
        try {
            $matches = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'matches' => $matches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching completed matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // GET MATCH DETAILS
    // ================================
    public function getMatchDetails($id)
    {
        try {
            $match = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])
            ->where('matchId', $id)
            ->firstOrFail();

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

    // ================================
    // GET APPROVED DONATIONS FOR MATCHING
    // ================================
    public function getApprovedDonations()
    {
        try {
            $donations = Donation::with(['donor.user'])
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'donations' => $donations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching approved donations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // GET APPROVED REQUESTS FOR MATCHING
    // ================================
    public function getApprovedRequests()
    {
        try {
            $requests = ReceiverRequest::with(['receiver.user'])
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'requests' => $requests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching approved requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // GET APPROVED INTERESTS FOR MATCHING
    // ================================
    public function getApprovedInterests()
    {
        try {
            $interests = Interest::with(['donor.user', 'request.receiver.user'])
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'interests' => $interests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching approved interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ======================================
    // FLOW A: MATCH USING APPROVED INTEREST
    // ======================================
    public function matchByInterest($interestId)
    {
        try {
            $interest = Interest::where('interestId', $interestId)
                ->where('status', 'approved')
                ->firstOrFail();

            $request = ReceiverRequest::where('requestId', $interest->requestId)
                ->where('status', 'approved')
                ->firstOrFail();

            $match = Matches::create([
                'interestId' => $interest->interestId,
                'donationId' => null,
                'requestId'  => $request->requestId,
                'status'     => 'approved',
                'matchType'  => 'interest'
            ]);

             // Update interest status to 'matched' so it won't appear in approved list
            $interest->status = 'matched';
            $interest->save();

            // Update statuses
            $request->status = 'matched';
            $request->save();

            // Load relationships
            $match->load(['donation.donor.user', 'request.receiver.user', 'interest.donor.user']);

            // SEND NOTIFICATIONS
            // Get donor user (from interest)
            $donorUser = $interest->donor->user;
            
            // Get receiver user (from request)
            $receiverUser = $request->receiver->user;

            // Send notification to donor - pass 'donor' as userType
            $donorUser->notify(new MatchCreatedNotification($match, 'donor'));

            // Send notification to receiver - pass 'receiver' as userType
            $receiverUser->notify(new MatchCreatedNotification($match, 'receiver'));

            // Optional: Send notification to admins - pass 'admin' as userType
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new MatchCreatedNotification($match, 'admin'));
            }

            return response()->json([
                'message' => 'Match created successfully using interest',
                'match' => $match
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ======================================
    // FLOW B: MANUAL MATCH (NO INTEREST)
    // ======================================
    public function manualMatch(Request $request)
    {
        try {
            $request->validate([
                'donationId' => 'required|exists:donations,donationId',
                'requestId'  => 'required|exists:requests,requestId',
            ]);

            $donation = Donation::where('donationId', $request->donationId)
                ->where('status', 'approved')
                ->firstOrFail();

            $receiverRequest = ReceiverRequest::where('requestId', $request->requestId)
                ->where('status', 'approved')
                ->firstOrFail();

            $match = Matches::create([
                'donationId' => $donation->donationId,
                'requestId'  => $receiverRequest->requestId,
                'interestId' => null,
                'status'     => 'approved',
                'matchType'  => 'manual'
            ]);

            $donation->status = 'matched';
            $donation->save();

            $receiverRequest->status = 'matched';
            $receiverRequest->save();

            // Load relationships
            $match->load(['donation.donor.user', 'request.receiver.user']);

             // SEND NOTIFICATIONS
            // Get donor user
            $donorUser = $donation->donor->user;
            
            // Get receiver user
            $receiverUser = $receiverRequest->receiver->user;

            // Send notification to donor - pass 'donor' as userType
            $donorUser->notify(new MatchCreatedNotification($match, 'donor'));

            // Send notification to receiver - pass 'receiver' as userType
            $receiverUser->notify(new MatchCreatedNotification($match, 'receiver'));

            // Optional: Send notification to admins - pass 'admin' as userType
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new MatchCreatedNotification($match, 'admin'));
            }

            return response()->json([
                'message' => 'Manual match created successfully',
                'match' => $match
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating manual match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // GET MATCHED INTERESTS (for reference)
    // ================================
    public function getMatchedInterests()
    {
        try {
            $interests = Interest::with(['donor.user', 'request.receiver.user'])
                ->where('status', 'matched')
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'interests' => $interests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching matched interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}