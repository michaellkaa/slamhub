<?php

namespace App\Http\Controllers;


use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return auth()->user()
            ->conversations()
            ->with(['users', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->get();
    }

    public function startConversation($userId)
    {
        $authId = auth()->id();
        $recipient = User::findOrFail($userId);

        if ((int) $recipient->id === (int) $authId) {
            return response()->json(['message' => 'Cannot start conversation with yourself.'], 422);
        }

        $conversation = auth()->user()
            ->conversations()
            ->whereHas('users', fn($q) => $q->where('users.id', $recipient->id))
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$authId, $recipient->id]);
        }

        return $conversation->load('users');
    }

    public function getMessages($id)
    {
        $conversation = auth()->user()
            ->conversations()
            ->where('conversations.id', $id)
            ->first();

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $sinceId = request()->integer('since_id');
        $limit = min(max(request()->integer('limit', 50), 1), 100);

        $query = Message::with('sender')
            ->where('conversation_id', $conversation->id);

        if ($sinceId) {
            return $query
                ->where('id', '>', $sinceId)
                ->orderBy('id')
                ->get();
        }

        return $query
            ->latest('id')
            ->limit($limit)
            ->get()
            ->sortBy('id')
            ->values();
    }

    public function sendMessage(Request $request, $id)
    {
        $conversation = auth()->user()
            ->conversations()
            ->where('conversations.id', $id)
            ->first();

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => (string) $request->input('body', '')
        ]);

        return $message->load('sender');
    }
}
