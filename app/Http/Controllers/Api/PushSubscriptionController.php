<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function vapidPublicKey()
    {
        $key = config('services.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');

        if (!$key) {
            return response()->json(['message' => 'VAPID public key is not configured.'], 503);
        }

        return response()->json([
            'publicKey' => $key,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string', 'max:50'],
        ]);

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'user_id' => $request->user()->id,
                'p256dh' => $validated['keys']['p256dh'],
                'auth' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aesgcm',
            ]
        );

        return response()->json([
            'id' => $subscription->id,
            'message' => 'Push subscription saved.',
        ], 201);
    }

    public function destroy(Request $request)
    {
        $userId = $request->user()->id;
        $endpoint = $request->input('endpoint');

        $query = PushSubscription::where('user_id', $userId);

        if (is_string($endpoint) && $endpoint !== '') {
            $query->where('endpoint', $endpoint);
        }

        $query->delete();

        return response()->json(['message' => 'Push subscription removed.']);
    }
}
