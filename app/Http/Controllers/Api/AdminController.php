<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donation;  
use Illuminate\Http\Request;


class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome Admin'
        ]);
    }

    public function allUsers()
    {
        return User::with(['donor', 'receiver'])
            ->select('userId','name','email','role','status')
            ->get();
    }

    // VIEW ALL PENDING DONATIONS
    public function pendingDonations()
    {
        $donations = Donation::where('status', 'pending')->get();

        return response()->json([
            'donations' => $donations
        ]);
    }

    // APPROVE DONATION
    public function approveDonation($id)
    {
        $donation = Donation::where('donationId', $id)->firstOrFail();

        if ($donation->status !== 'pending') {
            return response()->json([
                'message' => 'Donation already processed'
            ], 400);
        }

        $donation->status = 'approved';
        $donation->save();

        return response()->json([
            'message' => 'Donation approved successfully',
            'donation' => $donation
        ]);
    }

    // REJECT DONATION
    public function rejectDonation(Request $request, $id)
    {
        $donation = Donation::where('donationId', $id)->firstOrFail();

        if ($donation->status !== 'pending') {
            return response()->json([
                'message' => 'Donation already processed'
            ], 400);
        }

        $donation->status = 'rejected';
        $donation->save();

        return response()->json([
            'message' => 'Donation rejected successfully',
            'donation' => $donation
        ]);
    }
}

