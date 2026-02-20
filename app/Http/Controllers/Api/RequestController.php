<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request;
use App\Models\Matches; // Add this line
use App\Models\Interest;
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

        return Request::where('receiverId', $receiver->id)->orderBy('created_at', 'desc')->get();
    }

    // New method for detailed view (returns wrapped object)
    public function myRequestsWithDetails(HttpRequest $request)
    {
        $receiver = $request->user()->receiver;
        $requests = Request::where('receiverId', $receiver->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'requests' => $requests // Wrapped in object
        ]);
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

    // VIEW MY MATCHES (as receiver) - FIXED VERSION
    public function myMatches(HttpRequest $request)
    {
        try {
            $receiver = $request->user()->receiver;
            
            if (!$receiver) {
                return response()->json([
                    'message' => 'Receiver profile not found'
                ], 404);
            }

            // First, get all request IDs for this receiver
            $requestIds = Request::where('receiverId', $receiver->id)
                ->pluck('requestId')
                ->toArray();

            // Then get matches for those request IDs
            $matches = Matches::with([
                'donation.donor.user',
                'request',
                'interest.donor.user'
            ])
            ->whereIn('requestId', $requestIds)
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

    // VIEW SINGLE MATCH DETAILS FOR RECEIVER
    public function getReceiverMatchDetails($id)
    {
        try {
            $receiver = auth()->user()->receiver;
            
            if (!$receiver) {
                return response()->json([
                    'message' => 'Receiver profile not found'
                ], 404);
            }

            $match = Matches::with([
                'donation.donor.user',
                'request',
                'interest.donor.user'
            ])
            ->where('matchId', $id)
            ->whereHas('request', function($query) use ($receiver) {
                $query->where('receiverId', $receiver->id);
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

    /**
 * Receiver requests to mark match as executed
 */
    public function requestExecution($matchId)
    {
        try {
            $receiver = auth()->user()->receiver;
            
            $match = Matches::where('matchId', $matchId)
                ->whereHas('request', function($q) use ($receiver) {
                    $q->where('receiverId', $receiver->id);
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
            $match->execution_requested_by = 'receiver';
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