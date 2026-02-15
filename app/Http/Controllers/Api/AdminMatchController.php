<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Request as ReceiverRequest;
use App\Models\Interest;
use App\Models\Matches;
use Illuminate\Http\Request;

class AdminMatchController extends Controller
{
    // ======================================
    // FLOW A: MATCH USING APPROVED INTEREST
    // ======================================
    public function matchByInterest($interestId)
    {
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
            'matchType'  => 'interest',
            'status'     => 'approved'
        ]);

        // update statuses

        $request->status = 'matched';
        $request->save();

        return response()->json([
            'message' => 'Match created using interest',
            'match' => $match
        ]);
    }

    // ======================================
    // FLOW B: MANUAL MATCH (NO INTEREST)
    // ======================================
    public function manualMatch(Request $request)
    {
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
            'matchType'  => 'manual',
            'status'     => 'approved'
        ]);

        $donation->status = 'matched';
        $donation->save();

        $receiverRequest->status = 'matched';
        $receiverRequest->save();

        return response()->json([
            'message' => 'Manual match created',
            'match' => $match
        ]);
    }
}

