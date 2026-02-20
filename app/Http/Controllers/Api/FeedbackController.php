<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Matches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    /**
     * Get all feedback for the authenticated user
     */
    public function myFeedback()
    {
        try {
            $user = Auth::user();
            
            $feedback = Feedback::with(['match'])
                ->where('userId', $user->userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching my feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get completed matches eligible for feedback
     */
    public function getEligibleMatches()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get all completed matches
            $matches = Matches::with([
                'donation.donor.user',
                'request.receiver.user',
                'interest.donor.user'
            ])
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->get();

            // Filter matches based on user role and check if feedback already exists
            $eligibleMatches = $matches->filter(function($match) use ($user) {
                // Check if user is part of this match
                $isPartOfMatch = false;
                
                if ($user->role === 'donor') {
                    // Check if user is donor through donation
                    if ($match->donation && $match->donation->donor) {
                        if ($match->donation->donor->userId == $user->userId) {
                            $isPartOfMatch = true;
                        }
                    }
                    // Check if user is donor through interest
                    if (!$isPartOfMatch && $match->interest && $match->interest->donor) {
                        if ($match->interest->donor->userId == $user->userId) {
                            $isPartOfMatch = true;
                        }
                    }
                } else if ($user->role === 'receiver') {
                    // Check if user is receiver through request
                    if ($match->request && $match->request->receiver) {
                        if ($match->request->receiver->userId == $user->userId) {
                            $isPartOfMatch = true;
                        }
                    }
                }

                if (!$isPartOfMatch) {
                    return false;
                }

                // Check if user has already given feedback for this match
                $existingFeedback = Feedback::where('userId', $user->userId)
                    ->where('matchId', $match->matchId)
                    ->exists();

                return !$existingFeedback;
            })->values();

            return response()->json([
                'matches' => $eligibleMatches
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching eligible matches: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching eligible matches',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Submit feedback
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'matchId' => 'nullable|exists:matches,matchId',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'category' => 'required|in:donation_experience,request_experience,matching_process,communication,platform_usability,other',
                'is_anonymous' => 'boolean'
            ]);

            $user = Auth::user();

            // Check if feedback already exists for this match and user
            if ($request->matchId) {
                $existingFeedback = Feedback::where('userId', $user->userId)
                    ->where('matchId', $request->matchId)
                    ->first();

                if ($existingFeedback) {
                    return response()->json([
                        'message' => 'You have already provided feedback for this match'
                    ], 400);
                }
            }

            $feedback = Feedback::create([
                'userId' => $user->userId,
                'userRole' => $user->role,
                'matchId' => $request->matchId,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'category' => $request->category,
                'is_anonymous' => $request->is_anonymous ?? false,
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Feedback submitted successfully',
                'feedback' => $feedback->load('match')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error submitting feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error submitting feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update feedback
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            $feedback = Feedback::where('feedbackId', $id)
                ->where('userId', $user->userId)
                ->firstOrFail();

            // Only allow updates if feedback is still pending
            if ($feedback->status !== 'pending') {
                return response()->json([
                    'message' => 'Cannot update feedback that has already been processed'
                ], 400);
            }

            $request->validate([
                'rating' => 'sometimes|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'category' => 'sometimes|in:donation_experience,request_experience,matching_process,communication,platform_usability,other',
                'is_anonymous' => 'sometimes|boolean'
            ]);

            $feedback->update($request->only([
                'rating', 'comment', 'category', 'is_anonymous'
            ]));

            return response()->json([
                'message' => 'Feedback updated successfully',
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating feedback',
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
            $user = Auth::user();
            
            $feedback = Feedback::where('feedbackId', $id)
                ->where('userId', $user->userId)
                ->firstOrFail();

            // Only allow deletion if feedback is still pending
            if ($feedback->status !== 'pending') {
                return response()->json([
                    'message' => 'Cannot delete feedback that has already been processed'
                ], 400);
            }

            $feedback->delete();

            return response()->json([
                'message' => 'Feedback deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}