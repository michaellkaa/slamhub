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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
});

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