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

// Served via PHP so nginx/Cloudflare get correct SW headers (not a static cached file).
Route::get('/service-worker.js', function () {
    $candidates = [
        public_path('build/sw.js'),
        public_path('sw.js'),
    ];

    $path = null;
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $path = $candidate;
            break;
        }
    }

    if (!$path) {
        abort(404, 'Service worker not built. Run npm run build.');
    }

    return response()->file($path, [
        'Content-Type' => 'application/javascript; charset=UTF-8',
        'Service-Worker-Allowed' => '/',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
})->name('service-worker');

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
