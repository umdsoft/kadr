<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Аудит журналини даврий тозалаш (config/activitylog.php: clean_after_days).
// Жадвал чексиз ўсиб кетмаслиги учун. Серверда `schedule:run` крон ишлаши шарт.
Schedule::command('activitylog:clean')->daily();
