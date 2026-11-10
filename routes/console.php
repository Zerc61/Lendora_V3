<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\QueueService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('loans:remind-due')->dailyAt('08:00');

Schedule::call(fn () => app(QueueService::class)->prune(
    (int) config('lendora.queue_retention_days', 7),
))->dailyAt('02:00')->name('queue:prune');
