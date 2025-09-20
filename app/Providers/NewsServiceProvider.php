<?php

namespace App\Providers;

use App\Services\Contracts\NewsSourceInterface;
use App\Services\NewsServices\GuardianApiService;
use App\Services\NewsAggregatorService;
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
        // Register news sources
        $this->app->singleton(NewsApiService::class, function ($app) {
            return new NewsApiService(config('services.news_api.key'));
        });

        $this->app->singleton(GuardianApiService::class, function ($app) {
            return new GuardianApiService(config('services.guardian.key'));
        });

        $this->app->singleton(NewYorkTimesApiService::class, function ($app) {
            return new NewYorkTimesApiService(
                config('services.nyt.key'),
                config('services.nyt.secret')
            );
        });

        // Register news aggregator service
        $this->app->singleton(NewsAggregatorService::class, function ($app) {
            $aggregator = new NewsAggregatorService();
            
            // Add news sources to aggregator
            $aggregator->addSource($app->make(NewsApiService::class));
            $aggregator->addSource($app->make(GuardianApiService::class));
            $aggregator->addSource($app->make(NewYorkTimesApiService::class));
            
            return $aggregator;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}