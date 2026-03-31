<?php

namespace App\Http\Controllers;


use App\Models\Conversation;
use App\Models\Message;
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

        $conversation = Conversation::whereHas('users', fn($q) =>
            $q->where('user_id', $authId)
        )->whereHas('users', fn($q) =>
            $q->where('user_id', $userId)
        )->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$authId, $userId]);
        }

        return $conversation->load('users');
    }

    public function getMessages($id)
    {
        return Message::with('sender')
            ->where('conversation_id', $id)
            ->orderBy('created_at')
            ->get();
    }

    public function sendMessage(Request $request, $id)
    {
        $message = Message::create([
            'conversation_id' => $id,
            'sender_id' => auth()->id(),
            'body' => $request->body
        ]);

        return $message->load('sender');
    }
}
