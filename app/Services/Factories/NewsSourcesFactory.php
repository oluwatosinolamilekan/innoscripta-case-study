<?php

namespace App\Services\Factories;

use App\Services\Contracts\NewsSourceInterface;
use App\Services\NewsServices\GuardianApiService;
use App\Services\NewsServices\NewsApiService;
use App\Services\NewsServices\NewYorkTimesApiService;

class NewsSourcesFactory
{
    /**
     * Create and return all configured news sources.
     *
     * @return array<NewsSourceInterface>
     */
    public static function createAll(): array
    {
        return [
            self::createNewsApiService(),
            self::createGuardianApiService(),
            self::createNewYorkTimesApiService(),
        ];
    }

    /**
     * Create a NewsApiService instance.
     *
     * @return NewsApiService
     */
    public static function createNewsApiService(): NewsApiService
    {
        return new NewsApiService(config('services.news_api.key'));
    }

    /**
     * Create a GuardianApiService instance.
     *
     * @return GuardianApiService
     */
    public static function createGuardianApiService(): GuardianApiService
    {
        return new GuardianApiService(config('services.guardian.key'));
    }

    /**
     * Create a NewYorkTimesApiService instance.
     *
     * @return NewYorkTimesApiService
     */
    public static function createNewYorkTimesApiService(): NewYorkTimesApiService
    {
        return new NewYorkTimesApiService(
            config('services.nyt.key'),
            config('services.nyt.secret')
        );
    }
}
