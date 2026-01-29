<?php

use Illuminate\Support\Facades\Schedule;

// Cleanup tasks
Schedule::command('cleanup:all')->dailyAt('01:00');
Schedule::command('blacklist:cleanup')->hourly();

// Statistics
Schedule::command('stats:daily')->dailyAt('00:05');

// Reset counters
Schedule::command('minutes:reset-daily')->dailyAt('00:01');
Schedule::command('minutes:reset-monthly')->monthlyOn(1, '00:10');

// Queue restart to pick up any code changes
Schedule::command('queue:restart')->dailyAt('04:00');
