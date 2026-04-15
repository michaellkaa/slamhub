<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = min(max((int) $request->query('limit', 30), 1), 100);
        $onlyUnread = filter_var($request->query('unread', false), FILTER_VALIDATE_BOOL);

        $query = $request->user()->notifications()->latest();
        if ($onlyUnread) {
            $query = $request->user()->unreadNotifications()->latest();
        }

        return response()->json($query->limit($limit)->get());
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
