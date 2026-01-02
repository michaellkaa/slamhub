<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class AwardController extends Controller
{

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['organizer', 'admin'])) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            $data['icon_path'] = $request->file('icon')->store('awards', 'public');
        }

        $data['created_by'] = auth()->id();

        $award = Award::create($data);

        return response()->json($award, 201);
    }


    public function assign(Request $request)
    {
        if (!in_array(auth()->user()->role, ['organizer', 'admin'])) {
            abort(403);
        }

        $data = $request->validate([
            'award_id' => 'required|exists:awards,id',
            'user_id'  => 'required|exists:users,id',
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::with('performers')->findOrFail($data['event_id']);

        if (!$event->performers->contains($data['user_id'])) {
            return response()->json([
                'message' => 'Performer was not part of this event.'
            ], 422);
        }

        $award = Award::findOrFail($data['award_id']);

        $award->recipients()->syncWithoutDetaching([
            $data['user_id'] => ['event_id' => $data['event_id']]
        ]);

        return response()->json(['message' => 'Award assigned']);
    }

    public function userAwards($id)
    {
        $user = User::with(['awards' => function ($q) {
            $q->with('creator');
        }])->findOrFail($id);

        return response()->json($user->awards);
    }
}
