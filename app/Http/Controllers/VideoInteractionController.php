<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoComment;
use Illuminate\Http\Request;

class VideoInteractionController extends Controller
{
    public function toggleLike(Request $request, Video $video)
    {
        $like = $video->likes()->where('user_id', $request->user()->id)->first();

        if ($like) {
            $like->delete();

            return response()->json([
                'liked' => false,
                'likes_count' => $video->likes()->count(),
            ]);
        }

        $video->likes()->create([
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'liked' => true,
            'likes_count' => $video->likes()->count(),
        ], 201);
    }

    public function comments(Video $video)
    {
        $comments = $video->comments()
            ->with('user:id,username,name,profile_pic')
            ->with(['replies.user:id,username,name,profile_pic'])
            ->whereNull('parent_id')
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function storeComment(Request $request, Video $video)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|integer|exists:video_comments,id',
        ]);

        $parentId = $validated['parent_id'] ?? null;
        if ($parentId) {
            $parent = VideoComment::findOrFail($parentId);
            if ((int) $parent->video_id !== (int) $video->id) {
                return response()->json(['message' => 'Invalid parent comment'], 422);
            }
        }

        $comment = $video->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $parentId,
        ]);

        $comment->load('user:id,username,name,profile_pic');

        return response()->json($comment, 201);
    }

    public function destroyComment(Request $request, VideoComment $comment)
    {
        if ((int) $comment->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
