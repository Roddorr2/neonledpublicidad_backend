<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Run orchestrator in short, periodic runs. It processes one chunk per run by default.
Schedule::command('whatsapp:orchestrate --max-chunks=1')
    ->everyMinute()
    ->withoutOverlapping();
