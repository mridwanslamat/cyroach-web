<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

// Endpoint untuk menerima data dari ESP32
Route::post('/sensor-data', [SensorController::class, 'store']);

// Endpoint untuk frontend
Route::get('/devices/live', [DeviceController::class, 'live']);
Route::get('/missions', [MissionController::class, 'index']);
Route::get('/missions/{id}', [MissionController::class, 'show']);