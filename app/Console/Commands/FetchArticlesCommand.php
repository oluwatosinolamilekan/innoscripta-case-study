<?php

namespace App\Console\Commands;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch-articles {--source= : Specific news source to fetch from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from news sources';

    /**
     * Execute the console command.
     */
    public function handle(NewsAggregatorService $aggregator)
    {
        $this->info('Fetching articles from news sources...');

        $articles = $aggregator->getArticles();

        $this->info('Fetched ' . count($articles) . ' articles.');

        return Command::SUCCESS;
    }
}
