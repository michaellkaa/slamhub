<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\EventController;
use App\Http\Controllers\PerformerController;
use App\Http\Controllers\PostController;
use App\Models\User;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MessageController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
});

Route::middleware('auth:sanctum')->get('/me', [ApiUserController::class, 'me']);

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
});

Route::get('/performers', [PerformerController::class, 'index']);
Route::get('/events', [EventController::class, 'apiIndex']);

Route::get('/events/{id}', [EventController::class, 'show']);

Route::middleware('auth:sanctum')->get(
    '/profile/events',
    [EventController::class, 'profileEvents']
);

Route::get('/posts', [PostController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/profile/posts', [PostController::class, 'profilePosts']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/awards', [AwardController::class, 'store']);
    
    Route::post('/awards/assign', [AwardController::class, 'assign']);
    Route::get('/users/{id}/awards', [AwardController::class, 'userAwards']);

});
Route::get('/awards', [AwardController::class, 'index']);
Route::get('/profile/awards', [AwardController::class, 'profileAwards']);

Route::get('/search', [SearchController::class, 'index']);

Route::get('/users/{username}', [UserController::class, 'show']);

Route::get('/users/{username}/posts', [PostController::class, 'userPosts']);

Route::get('/users/{username}/events', [EventController::class, 'userEvents']);

Route::middleware('auth:sanctum')->post('/videos', [VideoController::class,'store']);
Route::get('/videos', [VideoController::class,'index']);
Route::get('/users/{username}/videos', [VideoController::class,'userVideos']);
Route::get('/videos/{slug}', [VideoController::class,'showBySlug']);

//Route::get('/users/{username}', action: [ApiUserController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{username}/follow-status', [FollowController::class, 'followStatus']);
    Route::post('/users/{username}/follow', [FollowController::class, 'toggleFollow']);
});

Route::get('/login', function () {
    return response()->json(['message' => 'Please login'], 401);
})->name('login');

Route::get('/users/{username}/followers', [FollowController::class, 'followersList']);
Route::get('/users/{username}/following', [FollowController::class, 'followingList']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/conversations', [MessageController::class, 'index']);
    Route::post('/conversations/{userId}', [MessageController::class, 'startConversation']);

    Route::get('/conversations/{id}/messages', [MessageController::class, 'getMessages']);
    Route::post('/conversations/{id}/messages', [MessageController::class, 'sendMessage']);
});

Route::middleware('auth:sanctum')->get('/users', [ApiUserController::class, 'index']);

