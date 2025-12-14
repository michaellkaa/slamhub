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

    return response()->json($event, 201);
}



    public function index()
    {
        $events = Event::with('performers', 'organizer')->orderBy('starts_at', 'desc')->get();
        return Inertia::render('Events/Index', [
            'events' => $events
        ]);
    }
}
