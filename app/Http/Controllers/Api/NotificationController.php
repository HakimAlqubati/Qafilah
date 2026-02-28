<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()->paginate(20);

        return NotificationResource::collection($notifications);
    }

    /**
     * Get all unread notifications for the authenticated user.
     */
    public function unread(Request $request)
    {
        $user = $request->user();

        $notifications = $user->unreadNotifications()->paginate(20);

        return NotificationResource::collection($notifications);
    }

    /**
     * Get count of unread notifications for the authenticated user.
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();

        return response()->json([
            'status' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $request->id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['status' => true, 'message' => 'Notification marked as read.']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['status' => true, 'message' => 'All notifications marked as read.']);
    }

    /**
     * Send a notification to a specific user.
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
            'image' => 'nullable|url',
            'data' => 'nullable|array',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            // Check if user has token (optional, but good for debugging response)
            if (!$user->fcm_token) {
                return response()->json([
                    'status' => false,
                    'message' => 'User does not have an FCM token.'
                ], 400);
            }

            $user->notify(new GeneralNotification(
                $request->title,
                $request->body,
                $request->data ?? [],
                $request->image
            ));

            return response()->json([
                'status' => true,
                'message' => 'Notification sent successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Notification Send Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the authenticated user's FCM token.
     */
    public function updateToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        if ($user) {
            $user->fcm_token = $request->fcm_token;
            $user->fcm_token_updated_at = now();
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Token updated successfully.'
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    }
}
