<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Store feedback (Donor / Receiver)
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'data' => $feedback,
        ], 201);
    }

    /**
     * List all feedback (Admin)
     */
    public function index()
    {
        $feedbacks = Feedback::with('user')->latest()->get();

        return response()->json(['data' => $feedbacks]);
    }

    /**
     * View single feedback (Admin)
     */
    public function show($id)
    {
        $feedback = Feedback::with('user', 'admin')->findOrFail($id);
        return response()->json(['data' => $feedback]);
    }

    /**
     * Reply to feedback (Admin)
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->reply = $request->reply;
        $feedback->replied_by = Auth::id();
        $feedback->replied_at = now();
        $feedback->save();

        return response()->json([
            'message' => 'Reply sent successfully',
            'data' => $feedback,
        ]);
    }

    /**
     * Delete feedback (Admin)
     */
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted successfully']);
    }

    /**
     * Feedback stats (Admin)
     */
    public function stats()
    {
        $total = Feedback::count();
        $replied = Feedback::whereNotNull('reply')->count();
        $pending = $total - $replied;

        return response()->json([
            'total_feedback' => $total,
            'replied' => $replied,
            'pending' => $pending,
        ]);
    }
}
