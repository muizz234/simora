<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Relay outbox domain_events ke transport yang dikonfigurasi.
Schedule::command('domain-events:relay')->everyMinute()->withoutOverlapping();
