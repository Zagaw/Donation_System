<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $perPage = $request->get('per_page', 20);
            $notifications = $user->notifications()->paginate($perPage);
            
            return response()->json([
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unreadCount()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $count = $user->unreadNotifications->count();
            
            return response()->json([
                'unread_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching unread count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $notification = $user->notifications()->where('id', $id)->first();
            
            if ($notification) {
                $notification->markAsRead();
            }
            
            return response()->json([
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error marking notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $user->unreadNotifications->markAsRead();
            
            return response()->json([
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error marking all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $notification = $user->notifications()->where('id', $id)->first();
            
            if ($notification) {
                $notification->delete();
            }
            
            return response()->json([
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clearAll()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $user->notifications()->delete();
            
            return response()->json([
                'message' => 'All notifications cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error clearing notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}