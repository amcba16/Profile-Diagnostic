<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class WebSearchService
{
    /**
     * Run a single web search.
     */
    public function search(string $query): array
    {
        $response = Http::timeout(3000)->get(
            'https://serpapi.com/search.json',
            [
                'engine' => 'google',
                'q' => $query,
                'api_key' => env('SERPAPI_KEY'),
                'gl' => 'ae',
                'hl' => 'en',
            ]
        );

        if ($response->failed()) {
            throw new \Exception(
                'Web search failed: ' . $response->body()
            );
        }

        return $response->json();
    }


    /**
     * Search for information about the identified person.
     *
     * All independent searches are executed in parallel
     * to reduce total execution time.
     */
    public function searchPerson(array $person): array
    {
        /*
        * Person data identified in Step 1.
        */
        $name = trim($person['name'] ?? '');
        $company = trim($person['company'] ?? '');
        $headline = trim($person['headline'] ?? '');
        $education = trim($person['education'] ?? '');
        $location = trim($person['location'] ?? '');


        /*
        * Create research queries.
        */
        $queries = [];


        /*
        * 1. Identity / professional searches.
        */
        if ($name && $company) {
            $queries[] =
                '"' . $name . '" "' . $company . '"';
        }


        if ($name && $headline) {
            $queries[] =
                '"' . $name . '" "' . $headline . '"';
        }


        if ($name && $education) {
            $queries[] =
                '"' . $name . '" "' . $education . '"';
        }


        if ($name && $location) {
            $queries[] =
                '"' . $name . '" "' . $location . '"';
        }


        /*
        * 2. Independent public-source searches.
        */
        if ($name) {

            $queries[] =
                '"' . $name . '" portfolio';

            $queries[] =
                '"' . $name . '" GitHub';

            $queries[] =
                '"' . $name . '" website';

            $queries[] =
                '"' . $name . '" developer';
        }


        /*
        * 3. Company-related searches.
        */
        if ($name && $company) {

            $queries[] =
                '"' . $name . '" "' . $company . '" website';

            $queries[] =
                '"' . $name . '" "' . $company . '" projects';
        }


        /*
        * Remove duplicate queries.
        */
        $queries = array_values(
            array_unique(
                array_filter($queries)
            )
        );


        /*
        * No valid queries.
        */
        if (empty($queries)) {

            return [
                'queries' => [],
                'results' => [],
                'failed_queries' => [],
            ];
        }


        /*
        * Run all searches in parallel.
        */
        $responses = Http::pool(
            function (Pool $pool) use ($queries) {

                $requests = [];

                foreach ($queries as $index => $query) {

                    $requests[] = $pool
                        ->as("search_$index")
                        ->timeout(120)
                        ->get(
                            'https://serpapi.com/search.json',
                            [
                                'engine' => 'google',
                                'q' => $query,
                                'api_key' => env('SERPAPI_KEY'),
                                'gl' => 'ae',
                                'hl' => 'en',
                            ]
                        );
                }

                return $requests;
            }
        );


        /*
        * Store successful research results.
        */
        $allResults = [];


        /*
        * Store queries that failed.
        */
        $failedQueries = [];


        /*
        * Prevent duplicate URLs.
        */
        $seenUrls = [];


        /*
        * Process each search response.
        */
        foreach ($queries as $index => $query) {

            $response = $responses["search_$index"];


            /*
            * If this search failed,
            * record the failure and continue.
            */
            if ($response->failed()) {

                $failedQueries[] = [
                    'query' => $query,
                    'error' =>
                        $response->json()['error']
                        ?? 'Search failed.',
                ];

                continue;
            }


            $results = $response->json();


            /*
            * Process organic Google results.
            */
            foreach (
                $results['organic_results'] ?? []
                as $result
            ) {

                $title = $result['title'] ?? '';
                $link = $result['link'] ?? '';
                $snippet = $result['snippet'] ?? '';
                $source = $result['source'] ?? null;


                /*
                * Ignore results without URLs.
                */
                if (!$link) {
                    continue;
                }


                /*
                * Remove duplicate URLs.
                */
                if (isset($seenUrls[$link])) {
                    continue;
                }


                /*
                * Ignore obvious irrelevant results.
                */
                $text = strtolower(
                    $title . ' ' .
                    $snippet . ' ' .
                    $link
                );


                $blockedWords = [
                    'tv series',
                    'imdb',
                    'netflix',
                    'rotten tomatoes',
                    'youtube',
                    'emergency room',
                    'wikipedia',
                    'merriam-webster',
                ];


                $irrelevant = false;


                foreach ($blockedWords as $word) {

                    if (str_contains($text, $word)) {

                        $irrelevant = true;

                        break;
                    }
                }


                if ($irrelevant) {
                    continue;
                }


                /*
                * Mark URL as already seen.
                */
                $seenUrls[$link] = true;


                /*
                * Store research result.
                */
                $allResults[] = [
                    'query' => $query,
                    'title' => $title,
                    'link' => $link,
                    'snippet' => $snippet,
                    'source' => $source,
                ];


                /*
                * Keep maximum 30 unique results.
                */
                if (count($allResults) >= 30) {
                    break;
                }
            }


            /*
            * Stop processing more results once
            * the 30-result limit is reached.
            */
            if (count($allResults) >= 30) {
                break;
            }
        }


        /*
        * Return Step 2 research data.
        */
        return [
            'person' => $person,

            'queries' => $queries,

            'results' => $allResults,

            'failed_queries' => $failedQueries,
        ];
    }


    /**
     * Search for verification evidence.
     *
     * All verification searches are executed in parallel.
     */
    public function searchVerificationEvidence(array $queries): array
    {
        if (empty($queries)) {
            return [];
        }

        /*
         * Run all verification searches at the same time.
         */
        $responses = Http::pool(function (Pool $pool) use ($queries) {

            $requests = [];

            foreach ($queries as $index => $query) {

                $requests[] = $pool
                    ->as("verification_$index")
                    ->timeout(120)
                    ->get(
                        'https://serpapi.com/search.json',
                        [
                            'engine' => 'google',
                            'q' => $query,
                            'api_key' => env('SERPAPI_KEY'),
                            'gl' => 'ae',
                            'hl' => 'en',
                        ]
                    );
            }

            return $requests;
        });


        $allResults = [];
        $seenUrls = [];

        /*
         * Process all responses.
         */
        foreach ($queries as $index => $query) {

            $response = $responses["verification_$index"];

            if ($response->failed()) {
                throw new \Exception(
                    'Web verification search failed: ' .
                    $response->body()
                );
            }

            $results = $response->json();

            foreach ($results['organic_results'] ?? [] as $result) {

                $title = $result['title'] ?? '';
                $link = $result['link'] ?? '';
                $snippet = $result['snippet'] ?? '';
                $source = $result['source'] ?? null;

                if (!$link) {
                    continue;
                }

                /*
                 * Remove duplicate URLs.
                 */
                if (isset($seenUrls[$link])) {
                    continue;
                }

                $seenUrls[$link] = true;

                $allResults[] = [
                    'title' => $title,
                    'link' => $link,
                    'snippet' => $snippet,
                    'source' => $source,
                    'query' => $query,
                ];
            }
        }

        return $allResults;
    }


    /**
     * Generate verification queries without using Gemini.
     *
     * This saves one Gemini API request per claim.
     */
    public function generateVerificationQueries(
        string $claim,
        array $person
    ): array {
        $name = trim($person['name'] ?? '');
        $company = trim($person['company'] ?? '');
        $location = trim($person['location'] ?? '');

        $queries = [];

        if ($name) {
            $queries[] =
                '"' . $name . '" "' . $claim . '"';
        }

        if ($name && $company) {
            $queries[] =
                '"' . $name . '" "' . $company . '"';
        }

        if ($name && $company) {
            $queries[] =
                '"' . $company . '" "' . $name . '"';
        }

        if ($name && $location) {
            $queries[] =
                '"' . $name . '" "' . $location . '"';
        }

        $queries[] =
            '"' . $claim . '"';

        return array_values(
            array_unique(
                array_filter($queries)
            )
        );
    }
}