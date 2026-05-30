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