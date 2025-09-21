<?php

namespace App\Providers;

use App\Services\Factories\NewsSourcesFactory;
use App\Services\NewsAggregatorService;
use App\Services\NewsServices\GuardianApiService;
use App\Services\NewsServices\NewsApiService;
use App\Services\NewsServices\NewYorkTimesApiService;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the factory
        $this->app->singleton(NewsSourcesFactory::class);
        
        // Register individual news sources
        // $this->app->singleton(NewsApiService::class, function ($app) {
        //     return NewsSourcesFactory::createNewsApiService();
        // });

        $this->app->singleton(GuardianApiService::class, function ($app) {
            return NewsSourcesFactory::createGuardianApiService();
        });

        // $this->app->singleton(NewYorkTimesApiService::class, function ($app) {
        //     return NewsSourcesFactory::createNewYorkTimesApiService();
        // });

        // Register news aggregator service
        // $this->app->singleton(NewsAggregatorService::class, function ($app) {
        //     // Use the static factory method instead of manual wiring
        //     return NewsAggregatorService::createWithDefaultSources();
        // });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register commands in the console kernel instead of here
    }
}