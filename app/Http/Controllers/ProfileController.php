<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }


    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    //tady update na admina
    public function promoteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['message' => 'uživatel už je admin'], 400);
        }

        $user->role = 'admin';
        $user->save();

        return response()->json(['message' => 'uživatel je nyní admin']);
    }
    
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'nullable|image|max:10240',
            'profile_picture' => 'nullable|image|max:10240',
        ]);

        $file = $request->file('photo') ?: $request->file('profile_picture');

        if (!$file) {
            return response()->json([
                'message' => 'Nebyl nahrán žádný obrázek.'
            ], 422);
        }

        $user = $request->user();

        if ($user->profile_pic && str_starts_with($user->profile_pic, 'profile_pics/')) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        $path = $file->store('profile_pics', 'public');

        $user->profile_pic = $path;
        $user->save();

        return response()->json([
            'message' => 'Profilovka úspěšně nahrána',
            'profile_pic' => $user->profile_pic,
            'profile_pic_url' => $user->profile_pic_url,
        ]);
    }

}