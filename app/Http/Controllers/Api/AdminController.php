<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donation;  
use App\Models\Request as ReceiverRequest;
use App\Models\Interest;  
use Illuminate\Http\Request;


class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome Admin'
        ]);
    }

    /*public function allUsers()
    {
        return User::with(['donor', 'receiver'])
            ->select('userId','name','email','role','status')
            ->get();
    }*/

            // Add this to your AuthController.php

   public function allUsers()
    {
        try {
            $users = User::with(['donor', 'receiver'])
                ->select('userId', 'name', 'email', 'phone', 'address', 'role', 'created_at')
                ->get();
            
            return response()->json([
                'users' => $users,
                'message' => 'Users retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Optional: Add pagination for better performance with many users
    public function allUsersPaginated(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Default 10 per page
        $page = $request->get('page', 1);
        
        $users = User::with(['donor', 'receiver'])
            ->select('userId', 'name', 'email', 'role', 'status', 'created_at')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl()
            ]
        ]);
    }

    // ================================
    // VIEW SINGLE DONATION (FULL DETAILS)
    // ================================
    public function showDonation($id)
    {
        $donation = Donation::with([
                'donor.user'
            ])
            ->where('donationId', $id)
            ->firstOrFail();

        return response()->json([
            'donation' => $donation
        ]);
    }

    // VIEW ALL PENDING DONATIONS
    public function pendingDonations()
    {
        $donations = Donation::with(['donor.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

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

    // ================================
    // VIEW ALL DONATIONS (ALL STATUSES)
    // ================================
    public function allDonations()
    {
        $donations = Donation::with('donor.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'donations' => $donations
        ]);
    }

        // VIEW ALL APPROVED DONATIONS
    public function approvedDonations()
    {
        $donations = Donation::with(['donor.user'])
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json([
            'donations' => $donations
        ]);
    }

    // VIEW ALL REJECTED DONATIONS
    public function rejectedDonations()
    {
        $donations = Donation::with(['donor.user'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'donations' => $donations
        ]);
    }

    // VIEW ALL EXECUTED DONATIONS
    public function executedDonations()
    {
        $donations = Donation::with(['donor.user'])
            ->where('status', 'executed')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'donations' => $donations
        ]);
    }

        // VIEW ALL MATECHED DONATIONS
    public function matchedDonations()
    {
        $donations = Donation::with(['donor.user'])
            ->where('status', 'matched')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'donations' => $donations
        ]);
    }

        // VIEW ALL COMPLETED DONATIONS
    public function completedDonations()
    {
        $donations = Donation::with(['donor.user'])
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'donations' => $donations
        ]);
    }

    // ================================
    // VIEW ALL REQUESTS (ALL STATUSES)
    // ================================
    public function allRequests()
    {
        $requests = ReceiverRequest::with('receiver.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // ================================
    // VIEW SINGLE REQUEST (FULL DETAILS)
    // ================================
    public function showRequest($id)
    {
        $request = ReceiverRequest::with([
                'receiver.user'
            ])
            ->where('requestId', $id)
            ->firstOrFail();

        return response()->json([
            'request' => $request
        ]);
    }

    // ================================
    // VIEW ALL PENDING REQUESTS
    // ================================
    public function pendingRequests()
    {
        $requests = ReceiverRequest::with(['receiver.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // ================================
    // APPROVE REQUEST
    // ================================
    public function approveRequest($id)
    {
        $request = ReceiverRequest::where('requestId', $id)->firstOrFail();

        if ($request->status !== 'pending') {
            return response()->json([
                'message' => 'Request already processed'
            ], 400);
        }

        $request->status = 'approved';
        $request->save();

        return response()->json([
            'message' => 'Request approved successfully',
            'request' => $request
        ]);
    }

    // ================================
    // REJECT REQUEST
    // ================================
    public function rejectRequest($id)
    {
        $request = ReceiverRequest::where('requestId', $id)->firstOrFail();

        if ($request->status !== 'pending') {
            return response()->json([
                'message' => 'Request already processed'
            ], 400);
        }

        $request->status = 'rejected';
        $request->save();

        return response()->json([
            'message' => 'Request rejected successfully',
            'request' => $request
        ]);
    }

    // ================================
    // VIEW ALL APPROVED REQUESTS
    // ================================
    public function approvedRequests()
    {
        $requests = ReceiverRequest::with(['receiver.user'])
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // ================================
    // VIEW ALL REJECTED REQUESTS
    // ================================
    public function rejectedRequests()
    {
        $requests = ReceiverRequest::with(['receiver.user'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // ================================
    // VIEW ALL EXECUTED REQUESTS
    // ================================
    public function executedRequests()
    {
        $requests = ReceiverRequest::with(['receiver.user'])
            ->where('status', 'executed')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // ================================
    // VIEW ALL MATCHED REQUESTS
    // ================================
    public function matchedRequests()
    {
        $requests = ReceiverRequest::with(['receiver.user'])
            ->where('status', 'matched')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // ================================
    // VIEW ALL COMPLETED REQUESTS
    // ================================
    public function completedRequests()
    {
        $requests = ReceiverRequest::with(['receiver.user'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // ================================
    // VIEW PENDING INTERESTS
    // ================================
    public function pendingInterests()
    {
        try {
            $interests = Interest::with(['donor.user', 'request.receiver.user'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'interests' => $interests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching pending interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // APPROVE INTEREST
    // ================================
    public function approveInterest($id)
    {
        $interest = Interest::where('interestId', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $interest->status = 'approved';
        $interest->save();

        return response()->json([
            'message' => 'Interest approved',
            'interest' => $interest
        ]);
    }

    // ================================
    // REJECT INTEREST
    // ================================
    public function rejectInterest($id)
    {
        $interest = Interest::where('interestId', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $interest->status = 'rejected';
        $interest->save();

        return response()->json([
            'message' => 'Interest rejected'
        ]);
    }

    // ================================
    // VIEW ALL INTERESTS
    // ================================
    public function allInterests()
    {
        try {
            $interests = Interest::with(['donor.user', 'request.receiver.user'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'interests' => $interests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching all interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // VIEW APPROVED INTERESTS
    // ================================
    public function approvedInterests()
    {
        try {
            $interests = Interest::with(['donor.user', 'request.receiver.user'])
                ->where('status', 'approved')
                ->orderBy('updated_at', 'desc')
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

    // ================================
    // VIEW REJECTED INTERESTS
    // ================================
    public function rejectedInterests()
    {
        try {
            $interests = Interest::with(['donor.user', 'request.receiver.user'])
                ->where('status', 'rejected')
                ->orderBy('updated_at', 'desc')
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

    // ================================
    // VIEW COMPLETED INTERESTS
    // ================================
    public function completedInterests()
    {
        try {
            $interests = Interest::with(['donor.user', 'request.receiver.user'])
                ->where('status', 'completed')
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'interests' => $interests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching completed interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStatusCounts()
    {
        return response()->json([
            'donations' => [
                'pending' => Donation::where('status', 'pending')->count(),
                'approved' => Donation::where('status', 'approved')->count(),
                'rejected' => Donation::where('status', 'rejected')->count(),
                'matched' => Donation::where('status', 'matched')->count(),
                'executed' => Donation::where('status', 'executed')->count(),
                'completed' => Donation::where('status', 'completed')->count(),
            ],
            'requests' => [
                'pending' => ReceiverRequest::where('status', 'pending')->count(),
                'approved' => ReceiverRequest::where('status', 'approved')->count(),
                'rejected' => ReceiverRequest::where('status', 'rejected')->count(),
                'matched' => ReceiverRequest::where('status', 'matched')->count(),
                'executed' => ReceiverRequest::where('status', 'executed')->count(),
                'completed' => ReceiverRequest::where('status', 'completed')->count(),
            ],
            'interests' => [
                'pending' => Interest::where('status', 'pending')->count(),
                'approved' => Interest::where('status', 'approved')->count(),
                'rejected' => Interest::where('status', 'rejected')->count(),
                'completed' => Interest::where('status', 'completed')->count(),
            ]
        ]);
    }


}

