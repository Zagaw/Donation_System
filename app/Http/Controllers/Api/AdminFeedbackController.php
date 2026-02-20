<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFeedbackController extends Controller
{
    /**
     * Get all feedback (with filtering)
     */
    public function index(Request $request)
    {
        try {
            $query = Feedback::with(['user', 'match']);

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by rating
            if ($request->has('rating') && $request->rating !== 'all') {
                $query->where('rating', $request->rating);
            }

            // Filter by category
            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            // Filter by user role
            if ($request->has('userRole') && $request->userRole !== 'all') {
                $query->where('userRole', $request->userRole);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $feedback = $query->orderBy('created_at', 'desc')->get();

            // Get statistics
            $stats = [
                'total' => Feedback::count(),
                'pending' => Feedback::where('status', 'pending')->count(),
                'approved' => Feedback::where('status', 'approved')->count(),
                'featured' => Feedback::where('status', 'featured')->count(),
                'average_rating' => round(Feedback::avg('rating') ?? 0, 1),
                'rating_distribution' => [
                    5 => Feedback::where('rating', 5)->count(),
                    4 => Feedback::where('rating', 4)->count(),
                    3 => Feedback::where('rating', 3)->count(),
                    2 => Feedback::where('rating', 2)->count(),
                    1 => Feedback::where('rating', 1)->count(),
                ],
                'category_breakdown' => [
                    'donation_experience' => Feedback::where('category', 'donation_experience')->count(),
                    'request_experience' => Feedback::where('category', 'request_experience')->count(),
                    'matching_process' => Feedback::where('category', 'matching_process')->count(),
                    'communication' => Feedback::where('category', 'communication')->count(),
                    'platform_usability' => Feedback::where('category', 'platform_usability')->count(),
                    'other' => Feedback::where('category', 'other')->count(),
                ]
            ];

            return response()->json([
                'feedback' => $feedback,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single feedback details
     */
    public function show($id)
    {
        try {
            $feedback = Feedback::with(['user', 'match'])->findOrFail($id);

            return response()->json([
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve feedback
     */
    public function approve($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            
            $feedback->status = 'approved';
            $feedback->save();

            return response()->json([
                'message' => 'Feedback approved successfully',
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error approving feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject feedback with response
     */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'admin_response' => 'required|string|max:500'
            ]);

            $feedback = Feedback::findOrFail($id);
            
            $feedback->status = 'rejected';
            $feedback->admin_response = $request->admin_response;
            $feedback->responded_at = now();
            $feedback->save();

            return response()->json([
                'message' => 'Feedback rejected successfully',
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error rejecting feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Feature feedback (show on landing page)
     */
    public function feature($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            
            // Only approved feedback can be featured
            if ($feedback->status !== 'approved') {
                return response()->json([
                    'message' => 'Only approved feedback can be featured'
                ], 400);
            }

            $feedback->status = 'featured';
            $feedback->save();

            return response()->json([
                'message' => 'Feedback featured successfully',
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error featuring feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add admin response to feedback
     */
    public function respond(Request $request, $id)
    {
        try {
            $request->validate([
                'admin_response' => 'required|string|max:1000'
            ]);

            $feedback = Feedback::findOrFail($id);
            
            $feedback->admin_response = $request->admin_response;
            $feedback->responded_at = now();
            $feedback->save();

            return response()->json([
                'message' => 'Response added successfully',
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete feedback
     */
    public function destroy($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            $feedback->delete();

            return response()->json([
                'message' => 'Feedback deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feedback statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_feedback' => Feedback::count(),
                'average_rating' => round(Feedback::avg('rating') ?? 0, 1),
                'total_comments' => Feedback::whereNotNull('comment')->count(),
                'anonymous_count' => Feedback::where('is_anonymous', true)->count(),
                
                'rating_distribution' => [
                    '5_stars' => Feedback::where('rating', 5)->count(),
                    '4_stars' => Feedback::where('rating', 4)->count(),
                    '3_stars' => Feedback::where('rating', 3)->count(),
                    '2_stars' => Feedback::where('rating', 2)->count(),
                    '1_star' => Feedback::where('rating', 1)->count(),
                ],
                
                'status_breakdown' => [
                    'pending' => Feedback::where('status', 'pending')->count(),
                    'approved' => Feedback::where('status', 'approved')->count(),
                    'rejected' => Feedback::where('status', 'rejected')->count(),
                    'featured' => Feedback::where('status', 'featured')->count(),
                ],
                
                'role_breakdown' => [
                    'donors' => Feedback::where('userRole', 'donor')->count(),
                    'receivers' => Feedback::where('userRole', 'receiver')->count(),
                ],
                
                'category_breakdown' => [
                    'donation_experience' => Feedback::where('category', 'donation_experience')->count(),
                    'request_experience' => Feedback::where('category', 'request_experience')->count(),
                    'matching_process' => Feedback::where('category', 'matching_process')->count(),
                    'communication' => Feedback::where('category', 'communication')->count(),
                    'platform_usability' => Feedback::where('category', 'platform_usability')->count(),
                    'other' => Feedback::where('category', 'other')->count(),
                ],
                
                'monthly_trend' => Feedback::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('AVG(rating) as avg_rating')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get()
            ];

            return response()->json([
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}