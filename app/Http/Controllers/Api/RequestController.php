<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    // CREATE REQUEST
    public function store(HttpRequest $request)
    {
        $request->validate([
            'itemName' => 'required|string',
            'category' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',

            // REQUIRED NRC fields
            'nrcNumber' => 'required|string',
            'nrcFrontImage' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'nrcBackImage' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $receiver = $request->user()->receiver;

        // Upload NRC images
        if ($request->hasFile('nrcFrontImage')) {
            $frontPath = $request->file('nrcFrontImage')->store('nrc', 'public');
        }

        if ($request->hasFile('nrcBackImage')) {
            $backPath = $request->file('nrcBackImage')->store('nrc', 'public');
        }

        $req = Request::create([
            'receiverId' => $receiver->id,
            'itemName' => $request->itemName,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'description' => $request->description,
            'status' => 'pending',
            'nrcNumber' => $request->nrcNumber,
            'nrcFrontImage' => $frontPath,
            'nrcBackImage' => $backPath
        ]);

        return response()->json([
            'message' => 'Request created successfully',
            'request' => $req
        ], 201);
    }

    // VIEW MY REQUESTS
    public function myRequests(HttpRequest $request)
    {
        $receiver = $request->user()->receiver;

        return Request::where('receiverId', $receiver->id)->get();
    }

    // DELETE REQUEST
    public function destroy(HttpRequest $request, $id)
    {
        $receiver = $request->user()->receiver;

        $req = Request::where('requestId', $id)
            ->where('receiverId', $receiver->id)
            ->firstOrFail();

        $req->delete();

        return response()->json([
            'message' => 'Request deleted successfully'
        ]);
    }

    /**
     * Get approved requests for donors to view
     */
    public function approvedRequests()
    {
        try {
            $requests = Request::with(['receiver.user'])
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($request) {
                    return [
                        'requestId' => $request->requestId,
                        'itemName' => $request->itemName,
                        'description' => $request->description,
                        'category' => $request->category,
                        'quantity' => $request->quantity,
                        'created_at' => $request->created_at,
                        'status' => $request->status,
                        'receiver' => [
                            'name' => $request->receiver->user->name,
                            'userId' => $request->receiver->userId,
                            'receiverType' => $request->receiver->receiverType
                        ]
                    ];
                });

            return response()->json([
                'requests' => $requests,
                'message' => 'Approved requests retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
