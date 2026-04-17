<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventPublishedNotification;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function createPage()
    {
        $performers = User::where('role', 'performer')->get();
        return Inertia::render('Events/Create', [
            'performers' => $performers
        ]);
    }


public function store(Request $request)
{
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'starts_at' => 'required|date',
        'description' => 'nullable|string',
        'ends_at' => 'nullable|date|after_or_equal:starts_at',
        'location' => 'nullable|string|max:255',
        'ticket_url' => 'nullable|url',
        'cover_image' => 'nullable|image|max:2048',
        'performers' => 'nullable|array',
        'performers.*' => 'exists:users,id',
        'guest_performers' => 'nullable|array',
        'guest_performers.*' => 'string|max:255',
        'event_mode' => 'nullable|in:regular,league',
        'is_award_event' => 'nullable|boolean',
        'winner_award_id' => 'nullable|exists:awards,id',
    ]);


    $data['user_id'] = Auth::id();
    $data['event_mode'] = $data['event_mode'] ?? 'regular';
    $data['is_award_event'] = (bool) ($data['is_award_event'] ?? false);
    if (!$data['is_award_event']) {
        $data['winner_award_id'] = null;
    }

    if ($request->hasFile('cover_image')) {
        $data['cover_image'] = $request->file('cover_image')
            ->store('events', 'public');
    }

    $event = Event::create($data);

if (!empty($data['performers'])) {
    $event->performers()->sync($data['performers']);
}

if (!empty($data['guest_performers'])) {
    $event->guest_performers = $data['guest_performers'];
    $event->save();
}


$organizer = Auth::user();

if ($organizer) {
    $followers = $organizer->followers()->get();

    foreach ($followers as $follower) {
        $follower->notify(
            new EventPublishedNotification($event, $organizer)
        );
    }
}






    return response()->json($event, 201);
}


public function show($id)
{
    $event = Event::with('performers', 'organizer', 'winnerAward')->findOrFail($id);
    return response()->json($event);
}


    public function index()
    {
        $events = Event::with('performers', 'organizer')->orderBy('starts_at', 'desc')->get();
        return Inertia::render('Events/Index', [
            'events' => $events
        ]);
    }

    public function apiIndex()
{
    $events = Event::with('performers', 'organizer', 'winnerAward')->orderBy('starts_at', 'desc')->get();
    return response()->json($events);
}

public function profileEvents()
{
    $user = Auth::user();

    $events = Event::where('user_id', $user->id)
        ->orderBy('starts_at', 'desc')
        ->get();

    return response()->json($events);
}

public function userEvents($username)
{
    $user = User::where('username', $username)->firstOrFail();

    $organizedEvents = Event::where('user_id', $user->id);

    $performedEvents = Event::whereHas('performers', function($q) use ($user) {
        $q->where('user_id', $user->id);
    });

    $events = $organizedEvents
        ->union($performedEvents)
        ->orderBy('starts_at', 'desc')
        ->get();

    return response()->json($events);
}

public function update(Request $request, $id)
{
    $event = Event::findOrFail($id);

    $user = Auth::user();

    if (!$user || ($user->role !== 'admin' && $user->id !== $event->user_id)) {
        return response()->json(['message' => 'Forbidden'], 403);
    }
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'starts_at' => 'required|date',
        'description' => 'nullable|string',
        'ends_at' => 'nullable|date|after_or_equal:starts_at',
        'location' => 'nullable|string|max:255',
        'ticket_url' => 'nullable|url',
        'cover_image' => 'nullable|image|max:2048',
        'performers' => 'nullable|array',
        'performers.*' => 'exists:users,id',
        'guest_performers' => 'nullable|array',
        'guest_performers.*' => 'string|max:255',
        'event_mode' => 'nullable|in:regular,league',
        'is_award_event' => 'nullable|boolean',
        'winner_award_id' => 'nullable|exists:awards,id',
    ]);

    $data['starts_at'] = Carbon::parse($data['starts_at']);
    if (!empty($data['ends_at'])) {
        $data['ends_at'] = Carbon::parse($data['ends_at']);
    }

    if ($request->hasFile('cover_image')) {
        $data['cover_image'] = $request->file('cover_image')
            ->store('events', 'public');
    }

    $event->update($data);

    if (!empty($data['performers'])) {
        $event->performers()->sync($data['performers']);
    }

    if (isset($data['guest_performers'])) {
        $event->guest_performers = $data['guest_performers'];
        $event->save();
    }

    return response()->json($event);
}
public function giveAward(Event $event, Request $request)
{
    $event->load('performers');

    if (!$event->is_award_event) {
        return response()->json(['message' => 'Not an award event'], 422);
    }

    if (!$request->award_id) {
        return response()->json(['message' => 'Missing award_id'], 422);
    }

    foreach ($event->performers as $user) {
        DB::table('award_user')->updateOrInsert(
            [
                'user_id' => $user->id,
                'award_id' => $request->award_id,
                'event_id' => $event->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    return response()->json([
        'message' => 'Awards given',
        'count' => $event->performers->count() 
    ]);
}

}
