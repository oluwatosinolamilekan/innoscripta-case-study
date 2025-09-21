<?php

namespace App\Console\Commands;

use App\Services\Factories\NewsSourcesFactory;
use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch {--source= : Specific source to fetch from (newsapi, guardian, nyt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from configured news sources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourceOption = $this->option('source');
        
        // Create news aggregator with appropriate sources
        $aggregator = new NewsAggregatorService();
        
        if ($sourceOption) {
            $this->info("Fetching articles from {$sourceOption}...");
            $source = $this->getSpecificSource($sourceOption);
            if (!$source) {
                $this->error("Invalid source: {$sourceOption}");
                return Command::FAILURE;
            }
            $aggregator->addSource($source);
        } else {
            $this->info('Fetching articles from all news sources...');
            foreach (NewsSourcesFactory::createAll() as $source) {
                $aggregator->addSource($source);
            }
        }
        
        // Fetch articles
        $articles = $aggregator->getArticles();
        
        $this->info('Fetched ' . count($articles) . ' articles.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Get a specific news source by name.
     *
     * @param string $sourceName
     * @return \App\Services\Contracts\NewsSourceInterface|null
     */
    private function getSpecificSource(string $sourceName)
    {
        return match (strtolower($sourceName)) {
            'newsapi' => NewsSourcesFactory::createNewsApiService(),
            'guardian' => NewsSourcesFactory::createGuardianApiService(),
            'nyt' => NewsSourcesFactory::createNewYorkTimesApiService(),
            default => null,
        };
    }
}
