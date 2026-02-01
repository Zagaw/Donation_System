<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    // CREATE DONATION
    public function store(Request $request)
    {
        $request->validate([
            'itemName' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        $donor = $request->user()->donor;

        $donation = Donation::create([
            'donorId' => $donor->id,
            'category' => $request->category,
            'itemName' => $request->itemName,
            'quantity' => $request->quantity,
            'description' => $request->description,
            'status' => 'pending'
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

        return Donation::where('donorId', $donor->id)->get();
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
}

