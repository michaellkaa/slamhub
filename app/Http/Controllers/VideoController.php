<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
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
            'user_id' => Auth::id(),
            'video_path' => $path,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'public',
        ]);

        return response()->json($video, 201);
    }

    // všechna videa
    public function index()
    {
        $videos = Video::with('user')->orderBy('created_at','desc')->get();
        return response()->json($videos);
    }

    public function userVideos($username)
    {
        $user = \App\Models\User::where('username',$username)->firstOrFail();

        $videos = Video::where('user_id',$user->id)
            ->where(function($q) use ($user) {
                if (Auth::id() !== $user->id) {
                    $q->whereIn('status',['public','unlisted']);
                }
            })
            ->orderBy('created_at','desc')
            ->get();

        return response()->json($videos);
    }

    public function showBySlug($slug)
    {
        $video = Video::where('slug',$slug)->firstOrFail();

        if ($video->status === 'private' && Auth::id() !== $video->user_id) {
            return response()->json(['message'=>'Unauthorized'],403);
        }

        return response()->json($video);
    }
}
