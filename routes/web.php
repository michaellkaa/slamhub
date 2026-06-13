<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::get('/test-mail', function () {
    Mail::raw('Test email ze SlamHubu', function ($message) {
        $message->to('ptackova@deware.eu')
            ->subject('SMTP test');
    });

    return 'Email odeslán';
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

Route::get('/test', function () {
    return Inertia::render('Test');
});


Route::middleware('auth')->group(function () {
    Route::get('/events/create', [EventController::class, 'createPage'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
});
