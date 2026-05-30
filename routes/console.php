<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('mission:check-timeout')->everyMinute();

Schedule::call(function () {
    \Illuminate\Support\Facades\Log::info('keep-alive ping');
})->everyMinute();