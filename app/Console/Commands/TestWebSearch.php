<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use GoogleSearchResults;

#[Signature('app:test-web-search')]
#[Description('Test SerpApi web search')]
class TestWebSearch extends Command
{
    public function handle()
    {
        $serp = new GoogleSearchResults(env('SERPAPI_KEY'));

        $results = $serp->get_json([
            'q' => '"John Smith" CEO UAE',
            'gl' => 'ae',
            'hl' => 'en',
        ]);

        foreach ($results->organic_results ?? [] as $result) {
            $this->info($result->title ?? '');
            $this->line($result->link ?? '');
            $this->line('');
        }

        return Command::SUCCESS;
    }
}