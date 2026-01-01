<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }

    public function profilePosts()
    {
        $posts = Post::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        Post::create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
            'status' => 1,
        ]);

        return response()->json([
            'message' => 'Post created successfully'
        ], 201);
    }

    public function show($id)
    {
        $post = Post::with('user')->findOrFail($id);
        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}
