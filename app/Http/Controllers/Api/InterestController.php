<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Models\Request as ReceiverRequest;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    // DONOR SHOW INTEREST (PLEDGE)
    public function store($requestId)
    {
        $donor = auth()->user()->donor;

        $request = ReceiverRequest::where('requestId', $requestId)
            ->where('status', 'approved')
            ->firstOrFail();

         // Check if request exists and is approved (not pending)
        $request = ReceiverRequest::where('requestId', $requestId)
            ->where('status', 'approved') // Changed from 'pending' to 'approved'
            ->first();

        if (!$request) {
            return response()->json([
                'message' => 'Request not found or not available for interest'
            ], 404);
        }

        // Check if already interested
        $existingInterest = Interest::where('donorId', $donor->id)
            ->where('requestId', $requestId)
            ->first();

        if ($existingInterest) {
            return response()->json([
                'message' => 'You have already shown interest in this request'
            ], 400);
        }

        $interest = Interest::create([
            'donorId'   => $donor->id,
            'requestId'=> $request->requestId,
            'status'   => 'pending'
        ]);

        return response()->json([
            'message' => 'Interest sent successfully',
            'interest' => $interest
        ], 201);
    }

    // DONOR VIEW OWN INTERESTS
    public function myInterests()
    {
        $donor = auth()->user()->donor;

        $interests = Interest::with('request')
            ->where('donorId', $donor->id)
            ->get();

        return response()->json($interests);
    }

    // DONOR CANCEL INTEREST
    public function destroy($id)
    {
        $donor = auth()->user()->donor;

        $interest = Interest::where('interestId', $id)
            ->where('donorId', $donor->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $interest->delete();

        return response()->json([
            'message' => 'Interest removed successfully'
        ]);
    }
}
