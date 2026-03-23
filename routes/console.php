<?php

use App\Models\MagicCode;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune expired magic codes (login, signup, email_change, account_deletion)
Schedule::call(function () {
    MagicCode::where('expires_at', '<', now())->delete();
})->hourly()->name('prune-magic-codes')->withoutOverlapping();

// Prune failed queue jobs older than 7 days
Schedule::command('queue:prune-failed --hours=168')->daily();

// Prune Spatie activity log entries older than 90 days
Schedule::command('activitylog:clean --days=90')->weekly();

// Prune expired personal access tokens
Schedule::command('sanctum:prune-expired --hours=24')->daily();
