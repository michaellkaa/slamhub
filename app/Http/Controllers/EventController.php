<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
    ]);

    $data['user_id'] = Auth::id();

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
    


    return response()->json($event, 201);
}


public function show($id)
{
    $event = Event::with('performers', 'organizer')->findOrFail($id);
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
    $events = Event::with('performers', 'organizer')->orderBy('starts_at', 'desc')->get();
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


}
