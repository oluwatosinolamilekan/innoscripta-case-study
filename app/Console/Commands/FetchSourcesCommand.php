<?php

namespace App\Console\Commands;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch-sources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch sources from news APIs';

    /**
     * Execute the console command.
     */
    public function handle(NewsAggregatorService $aggregator)
    {
        $this->info('Fetching sources from news APIs...');

        $sources = $aggregator->getSources();

        $this->info('Fetched ' . count($sources) . ' sources.');

        return Command::SUCCESS;
    }
}
