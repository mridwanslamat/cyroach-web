<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\MissionPdfController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/missions', function () {
    return view('missions');
})->name('missions.index');

Route::get('/missions/{id}', function ($id) {
    return view('mission-detail', ['id' => $id]);
})->name('missions.show');

Route::get('/missions/{id}/export-pdf', [MissionPdfController::class, 'export'])->name('missions.export-pdf');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::post('/broadcasting/auth', function(\Illuminate\Http\Request $request) {
    $pusher = new \Pusher\Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        ['cluster' => config('broadcasting.connections.pusher.options.cluster'), 'useTLS' => true]
    );
    $auth = $pusher->authorizeChannel($request->channel_name, $request->socket_id, json_encode([
        'user_id' => uniqid(),
        'user_info' => ['name' => 'Guest']
    ]));
    return response($auth);
});
