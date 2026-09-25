<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifications",
     *     tags={"Notifications"}, summary="List authenticated user's notifications", security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Paginated list with unread_count")
     * )
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications() // all notifications, read + unread
            ->latest()
            ->paginate(20);

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'data' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => class_basename($n->type), // e.g. "MilestoneReadyForApproval" instead of full namespace
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }
    /**
     * @OA\Post(
     *     path="/notifications/{id}/read",
     *     tags={"Notifications"}, summary="Mark one notification as read", security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Marked read")
     * )
     */
    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Marked as read']);
    }
    /**
     * @OA\Post(
     *     path="/notifications/read-all",
     *     tags={"Notifications"}, summary="Mark all notifications as read", security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="All marked read")
     * )
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
