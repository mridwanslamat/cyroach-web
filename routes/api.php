<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

// Endpoint untuk menerima data dari ESP32
Route::post('/sensor-data', [SensorController::class, 'store']);
Route::post('/end-mission', [SensorController::class, 'endMission']);

// Endpoint untuk frontend
Route::get('/devices/live', [DeviceController::class, 'live']);
Route::get('/missions', [MissionController::class, 'index']);
Route::get('/missions/{id}', [MissionController::class, 'show']);
Route::get('/mission-status', [MissionController::class, 'currentStatus']);

// Viewer counter
Route::get('/viewers', function () {
    $viewers = Cache::get('active_viewers', []);
    $ip = request()->ip();
    $viewers[$ip] = now()->timestamp;
    $viewers = array_filter($viewers, fn($ts) => now()->timestamp - $ts < 30);
    Cache::put('active_viewers', $viewers, 60);
    return response()->json(['count' => max(1, count($viewers))]);
});