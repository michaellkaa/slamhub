<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private const ROLES = ['user', 'performer', 'organizer', 'moderator', 'admin'];

    public function overview()
    {
        return response()->json([
            'users_total' => User::count(),
            'users_banned' => User::where('is_banned', true)->count(),
            'users_by_role' => User::query()
                ->selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role'),
            'posts_total' => Post::count(),
            'posts_hidden' => Post::where('status', 0)->count(),
            'videos_total' => Video::count(),
            'events_total' => Event::count(),
        ]);
    }

    public function users(Request $request)
    {
        $query = User::query()->orderByDesc('id');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            if (in_array($role, self::ROLES, true)) {
                $query->where('role', $role);
            }
        }

        if ($request->query('banned') === '1') {
            $query->where('is_banned', true);
        } elseif ($request->query('banned') === '0') {
            $query->where('is_banned', false);
        }

        $users = $query->paginate(20)->through(function (User $user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_banned' => (bool) $user->is_banned,
                'points' => (int) ($user->points ?? 0),
                'profile_pic_url' => $user->profile_pic_url,
                'created_at' => optional($user->created_at)?->toIso8601String(),
                'last_login_at' => optional($user->last_login_at)?->toIso8601String(),
            ];
        });

        return response()->json($users);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['sometimes', Rule::in(self::ROLES)],
            'is_banned' => ['sometimes', 'boolean'],
        ]);

        if ($request->user()->id === $user->id) {
            if (array_key_exists('role', $validated) && $validated['role'] !== 'admin') {
                return response()->json(['message' => 'Nemůžeš si odebrat admin roli.'], 422);
            }
            if (array_key_exists('is_banned', $validated) && $validated['is_banned']) {
                return response()->json(['message' => 'Nemůžeš zabanovat sám sebe.'], 422);
            }
        }

        $user->fill($validated);
        $user->save();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_banned' => (bool) $user->is_banned,
            'points' => (int) ($user->points ?? 0),
            'profile_pic_url' => $user->profile_pic_url,
            'created_at' => optional($user->created_at)?->toIso8601String(),
            'last_login_at' => optional($user->last_login_at)?->toIso8601String(),
        ]);
    }

    public function posts(Request $request)
    {
        $query = Post::with('user:id,username,name,profile_pic')
            ->orderByDesc('id');

        if ($request->query('hidden') === '1') {
            $query->where('status', 0);
        }

        $posts = $query->paginate(20)->through(function (Post $post) {
            return [
                'id' => $post->id,
                'body' => $post->body,
                'status' => (int) $post->status,
                'created_at' => optional($post->created_at)?->toIso8601String(),
                'user' => $post->user ? [
                    'id' => $post->user->id,
                    'username' => $post->user->username,
                    'name' => $post->user->name,
                    'profile_pic_url' => $post->user->profile_pic_url,
                ] : null,
            ];
        });

        return response()->json($posts);
    }

    public function updatePost(Request $request, Post $post)
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ]);

        $post->update(['status' => $validated['status']]);

        return response()->json([
            'id' => $post->id,
            'status' => (int) $post->status,
        ]);
    }

    public function videos(Request $request)
    {
        $query = Video::with('user:id,username,name,profile_pic')
            ->orderByDesc('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $videos = $query->paginate(20)->through(function (Video $video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'slug' => $video->slug,
                'status' => $video->status,
                'created_at' => optional($video->created_at)?->toIso8601String(),
                'user' => $video->user ? [
                    'id' => $video->user->id,
                    'username' => $video->user->username,
                    'name' => $video->user->name,
                    'profile_pic_url' => $video->user->profile_pic_url,
                ] : null,
            ];
        });

        return response()->json($videos);
    }

    public function updateVideo(Request $request, Video $video)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['public', 'private', 'unlisted'])],
        ]);

        $video->update(['status' => $validated['status']]);

        return response()->json([
            'id' => $video->id,
            'status' => $video->status,
        ]);
    }

    public function events()
    {
        $events = Event::with('organizer:id,username,name')
            ->orderByDesc('id')
            ->paginate(20)
            ->through(function (Event $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'location' => $event->location,
                    'starts_at' => optional($event->starts_at)?->toIso8601String(),
                    'event_mode' => $event->event_mode ?? null,
                    'user' => $event->organizer ? [
                        'id' => $event->organizer->id,
                        'username' => $event->organizer->username,
                        'name' => $event->organizer->name,
                    ] : null,
                ];
            });

        return response()->json($events);
    }
}
