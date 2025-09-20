<?php

namespace App\Console;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Fetch articles from news sources every hour
        $schedule->call(function () {
            $aggregator = app(NewsAggregatorService::class);
            $aggregator->getArticles();
        })->hourly();

        // Update sources once a day
        $schedule->call(function () {
            $aggregator = app(NewsAggregatorService::class);
            $aggregator->getSources();
        })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
