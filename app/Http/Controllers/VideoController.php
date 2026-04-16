<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class VideoController extends Controller
{
    private function currentUserId(): int
    {
        return (int) (Auth::guard('sanctum')->id() ?? Auth::id() ?? 0);
    }

    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,webm|max:204800', 
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:public,private,unlisted'
        ]);

        $path = $request->file('video')->store('videos', 'public');

        $video = Video::create([
            'user_id' => $request->user()->id,
            'video_path' => $path,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'public',
        ]);

        return response()->json([
            'slug' => $video->slug,
            'video_url' => $video->video_url,
        ], 201);
    }

    public function index()
    {
        $userId = $this->currentUserId();
        $likedByUserId = $userId ?: 0;

        $videos = Video::with('user')
            ->withCount([
                'likes',
                'comments',
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $likedByUserId),
            ])
            ->where(function($q) use ($userId){
                $q->where('status', '!=', 'private');
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($video) {
                $video->liked_by_me = (bool) $video->liked_by_me;
            });

        return response()->json($videos);
    }

    public function userVideos($username)
    {
        $user = \App\Models\User::where('username',$username)->firstOrFail();
        $likedByUserId = $this->currentUserId();

        $videos = Video::where('user_id',$user->id)
            ->with('user')
            ->withCount([
                'likes',
                'comments',
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $likedByUserId),
            ])
            ->where(function($q) use ($user) {
                if ($this->currentUserId() !== (int) $user->id) {
                    $q->whereIn('status',['public','unlisted']);
                }
            })
            ->orderBy('created_at','desc')
            ->get()
            ->each(function ($video) {
                $video->liked_by_me = (bool) $video->liked_by_me;
            });

        return response()->json($videos);
    }

    public function showBySlug($slug)
    {
        $likedByUserId = $this->currentUserId();

        $video = Video::with('user')
            ->withCount([
                'likes',
                'comments',
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $likedByUserId),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        if ($video->status === 'private' && $this->currentUserId() !== (int) $video->user_id) {
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $video->liked_by_me = (bool) $video->liked_by_me;

        return response()->json($video);
    }
}