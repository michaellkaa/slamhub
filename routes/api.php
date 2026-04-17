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
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\EventVotingController;
use App\Http\Controllers\EventVotingHostController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\VideoInteractionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EventLeagueController;


Route::post('/login', [AuthController::class, 'login']);
/*
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
*/
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ApiUserController::class, 'me']);
    Route::put('/me', [ApiUserController::class, 'updateMe']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
});

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);

});

Route::get('/performers', [PerformerController::class, 'index']);
Route::get('/events', [EventController::class, 'apiIndex']);

Route::get('/events/{id}', [EventController::class, 'show']);

Route::middleware('auth:sanctum')->get(
    '/profile/events',
    [EventController::class, 'profileEvents']
);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}/comments', [PostInteractionController::class, 'comments']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/profile/posts', [PostController::class, 'profilePosts']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/like', [PostInteractionController::class, 'toggleLike']);
    Route::post('/posts/{post}/comments', [PostInteractionController::class, 'storeComment']);
    Route::delete('/posts/comments/{comment}', [PostInteractionController::class, 'destroyComment']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/awards', [AwardController::class, 'store']);

    Route::post('/awards/assign', [AwardController::class, 'assign']);
    Route::get('/users/{id}/awards', [AwardController::class, 'userAwards']);
Route::get('/profile/awards', [AwardController::class, 'profileAwards']);

});
Route::get('/awards', [AwardController::class, 'index']);
Route::get('/awards/leaderboard', [AwardController::class, 'leaderboard']);
Route::get('/awards/league-progress', [AwardController::class, 'leagueProgress']);

Route::get('/search', [SearchController::class, 'index']);

Route::get('/users/{username}', [UserController::class, 'show']);

Route::get('/users/{username}/posts', [PostController::class, 'userPosts']);

Route::get('/users/{username}/events', [EventController::class, 'userEvents']);

Route::middleware('auth:sanctum')->post('/videos', [VideoController::class,'store']);
Route::get('/videos', [VideoController::class,'index']);
Route::get('/users/{username}/videos', [VideoController::class,'userVideos']);
Route::get('/videos/{slug}', [VideoController::class,'showBySlug']);
Route::get('/videos/{video}/comments', [VideoInteractionController::class, 'comments']);

//Route::get('/users/{username}', action: [ApiUserController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{username}/follow-status', [FollowController::class, 'followStatus']);
    Route::post('/users/{username}/follow', [FollowController::class, 'toggleFollow']);
    Route::post('/videos/{video}/like', [VideoInteractionController::class, 'toggleLike']);
    Route::post('/videos/{video}/comments', [VideoInteractionController::class, 'storeComment']);
    Route::delete('/videos/comments/{comment}', [VideoInteractionController::class, 'destroyComment']);
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

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
});

Route::middleware('auth:sanctum')->get('/users', [ApiUserController::class, 'index']);

Route::middleware('auth:sanctum')->get('/following', [FollowController::class, 'following']);

Route::get('/events/{event}/voting/status', [EventVotingController::class, 'sessionStatus']);
Route::get('/events/{event}/voting/live-round', [EventVotingController::class, 'liveRound']);
Route::post('/voting/join', [EventVotingController::class, 'joinByCode'])->middleware('throttle:voting-join');
Route::post('/events/{event}/voting/cast', [EventVotingController::class, 'castVote'])->middleware('throttle:voting-cast');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events/{event}/voting/session', [EventVotingHostController::class, 'upsertSession']);
    Route::patch('/events/{event}/voting/session/toggle', [EventVotingHostController::class, 'toggleEnabled']);
    Route::post('/events/{event}/voting/session/rotate-code', [EventVotingHostController::class, 'rotateCode']);
    Route::post('/events/{event}/voting/rounds', [EventVotingHostController::class, 'createRound']);
    Route::post('/events/{event}/voting/rounds/{round}/start', [EventVotingHostController::class, 'startRound']);
    Route::post('/events/{event}/voting/rounds/{round}/close', [EventVotingHostController::class, 'closeRound']);
    Route::patch('/events/{event}/voting/rounds/{round}/visibility', [EventVotingHostController::class, 'updateRoundVisibility']);
    Route::post('/events/{event}/voting/finalize', [EventVotingHostController::class, 'finalizeEventVoting']);
    Route::get('/events/{event}/voting/results/live', [EventVotingHostController::class, 'liveResults']);

});

    Route::get('/events/{event}/league', [EventLeagueController::class, 'show']);
    Route::put('/events/{event}/league', [EventLeagueController::class, 'update']);


    Route::post('/events/{event}/give-award', [EventController::class, 'giveAward']);

    Route::get('/users/{username}/awards', [AwardController::class, 'userAwards']);