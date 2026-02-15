<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matches;
use App\Models\Donation;
use App\Models\Request as ReceiverRequest;
use Illuminate\Http\Request;

class AdminExecutionController extends Controller
{
    // ======================================
    // STEP 1: EXECUTE DONATION
    // ======================================
    public function executeDonation($matchId)
    {
        $match = Matches::where('matchId', $matchId)
            ->where('status', 'approved')
            ->firstOrFail();

        // Donation may be null for some interest-based flows
        if ($match->donationId) {
            $donation = Donation::where('donationId', $match->donationId)->firstOrFail();
            $donation->status = 'executed';
            $donation->save();
        }

        $match->status = 'executed';
        $match->save();

        return response()->json([
            'message' => 'Donation executed successfully',
            'match'   => $match
        ]);
    }

    // ======================================
    // STEP 2: COMPLETE MATCH
    // ======================================
    public function completeMatch($matchId)
    {
        $match = Matches::where('matchId', $matchId)
            ->where('status', 'executed')
            ->firstOrFail();

        // Complete Donation
        if ($match->donationId) {
            $donation = Donation::where('donationId', $match->donationId)->firstOrFail();
            $donation->status = 'completed';
            $donation->save();
        }

        // Complete Request
        $request = ReceiverRequest::where('requestId', $match->requestId)->firstOrFail();
        $request->status = 'completed';
        $request->save();

        $match->status = 'completed';
        $match->save();

        return response()->json([
            'message' => 'Match completed successfully',
            'match'   => $match
        ]);
    }
}
