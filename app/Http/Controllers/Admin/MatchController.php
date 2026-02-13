<?php

namespace App\Http\Controllers\Admin;
$matchController = new MatchController();

use App\Models\Match;
use App\Models\Donation;
use App\Models\RequestModel;
use App\Models\User;
use App\Notifications\MatchNotification;

class MatchController extends Controller
{
    public function match(Request $request)
    {
        $request->validate([
            'donation_id' => 'required|exists:donations,id',
            'request_id' => 'required|exists:requests,id',
        ]);

        $donation = Donation::findOrFail($request->donation_id);
        $receiverRequest = RequestModel::findOrFail($request->request_id);

        // Create Match
        $match = DonationMatch::create([
            'donation_id' => $donation->id,
            'request_id' => $receiverRequest->id,
            'donor_id' => $donation->user_id,
            'receiver_id' => $receiverRequest->user_id,
            'status' => 'pending',
        ]);

        // Notify Donor
        $donation->user->notify(new MatchNotification($match, 'donor'));

        // Notify Receiver
        $receiverRequest->user->notify(new MatchNotification($match, 'receiver'));

        return response()->json([
            'message' => 'Match created and notifications sent successfully.'
        ]);
    }
    
}

