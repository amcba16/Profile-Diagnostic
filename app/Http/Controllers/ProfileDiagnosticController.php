<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiService;
use App\Services\WebSearchService;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfileDiagnosticController extends Controller
{

    /*
     * ================================================================
     * EXPORT PDF
     * ================================================================
     */

    public function exportPdf(Request $request)
    {
        $data = $request->input('data');

        if (!$data) {
            return redirect()
                ->route('profile.page')
                ->with('error', 'No diagnostic data available for export.');
        }

        $data = json_decode($data, true);

        if (!is_array($data)) {
            return redirect()
                ->route('profile.page')
                ->with('error', 'Invalid diagnostic data.');
        }

        $pdf = Pdf::loadView('profile-pdf', $data);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('profile-diagnostic.pdf');
    }


    /*
     * ================================================================
     * ANALYZE PROFILE
     * ================================================================
     */

    public function analyze(
        Request $request,
        AiService $aiService,
        WebSearchService $webSearchService
    ) {

        /*
         * ============================================================
         * STEP 1
         * Identify person from LinkedIn URL
         * ============================================================
         */

        $personData = $this->stepOneIdentifyPerson(
            $request,
            $aiService,
            $webSearchService
        );

        $person = $personData['person'] ?? [];


        /*
         * ============================================================
         * STEP 2
         * Research the identified person
         * ============================================================
         */

        $researchResults = $webSearchService->searchPerson(
            $person
        );

        $results = $researchResults['results'] ?? [];


        /*
         * ============================================================
         * STEP 3
         *
         * ONE AI CALL:
         *
         * - Identify relevant research results
         * - Extract factual claims
         * ============================================================
         */

        $analysis = $aiService->analyzeRelevantResults(
            $person,
            $results
        );


        /*
         * Get relevant result indexes
         */

        $relevantIndexes =
            $analysis['relevant_indexes'] ?? [];


        /*
         * Get extracted claims
         */

        $claims =
            $analysis['claims'] ?? [];


        /*
         * ============================================================
         * STEP 3.1
         * Convert relevant indexes into actual search results
         * ============================================================
         */

        $relevantResults = [];

        foreach ($relevantIndexes as $index) {

            if (isset($results[$index])) {

                $relevantResults[] =
                    $results[$index];
            }
        }


        /*
         * ============================================================
         * STEP 4
         * Verify extracted claims
         * ============================================================
         */

        $verification = $aiService->verifyClaimsEvidence(
            $claims,
            $person,
            $relevantResults
        );


        /*
         * ============================================================
         * RETURN RESULTS VIEW
         * ============================================================
         */

        return view('profile-results', [

            'success' => true,

            /*
             * STEP 1
             */
            'step1' => $personData,

            /*
             * STEP 2
             */
            'step2' => $researchResults,

            /*
             * STEP 3
             */
            'step3' => [
                'relevant_indexes' => $relevantIndexes,

                'relevant_results' => $relevantResults,

                'claims' => $claims,
            ],

            /*
             * STEP 4
             */
            'step4' => [
                'verification' => $verification,
            ],
        ]);
    }


    /*
     * ================================================================
     * STEP 1
     *
     * Identify person from LinkedIn URL
     * ================================================================
     */

    private function stepOneIdentifyPerson(
        Request $request,
        AiService $aiService,
        WebSearchService $webSearchService
    ): array {

        /*
         * ============================================================
         * STEP 1.1
         * Validate LinkedIn URL
         * ============================================================
         */

        $request->validate([
            'linkedin_url' => [
                'required',
                'url',
                'regex:/^https:\/\/([a-z]{2,3}\.)?linkedin\.com\/in\/.+/i',
            ],
        ]);


        /*
         * ============================================================
         * STEP 1.2
         * Get LinkedIn URL
         * ============================================================
         */

        $linkedinUrl = rtrim(
            trim(
                $request->input('linkedin_url')
            ),
            '/'
        );


        /*
         * ============================================================
         * STEP 1.3
         * Extract LinkedIn profile slug
         * ============================================================
         */

        $path = parse_url(
            $linkedinUrl,
            PHP_URL_PATH
        );


        $slug = trim(
            preg_replace(
                '#^/in/#i',
                '',
                $path ?? ''
            ),
            '/'
        );


        if (!$slug) {

            throw new \Exception(
                'Could not extract LinkedIn profile slug.'
            );
        }


        /*
         * ============================================================
         * STEP 1.4
         * Create SerpApi search query
         * ============================================================
         */

        $searchQuery =
            'site:linkedin.com/in/ "' .
            $slug .
            '"';


        /*
         * ============================================================
         * STEP 1.5
         * Search SerpApi
         * ============================================================
         */

        $searchResponse =
            $webSearchService->search(
                $searchQuery
            );


        $results =
            $searchResponse['organic_results'] ?? [];


        /*
         * ============================================================
         * STEP 1.6
         * Find exact LinkedIn profile
         *
         * Compare the PROFILE SLUG instead of the domain.
         *
         * This supports:
         *
         * www.linkedin.com/in/example
         * ae.linkedin.com/in/example
         * in.linkedin.com/in/example
         * de.linkedin.com/in/example
         * ============================================================
         */

        $matchedProfile = null;


        foreach ($results as $result) {

            $resultLink =
                $result['link'] ?? '';


            if (!$resultLink) {
                continue;
            }


            /*
             * Extract URL path
             */

            $resultPath = parse_url(
                $resultLink,
                PHP_URL_PATH
            );


            if (!$resultPath) {
                continue;
            }


            /*
             * Extract profile slug
             */

            $resultSlug = trim(
                preg_replace(
                    '#^/in/#i',
                    '',
                    $resultPath
                ),
                '/'
            );


            /*
             * Compare profile slug
             */

            if (
                strtolower($resultSlug) ===
                strtolower($slug)
            ) {

                $matchedProfile =
                    $result;

                break;
            }
        }


        /*
         * ============================================================
         * STEP 1.7
         * Fallback search
         *
         * Sometimes SerpApi does not return the profile with the
         * quoted search query. Try one broader query.
         * ============================================================
         */

        if (!$matchedProfile) {

            $fallbackQuery =
                'site:linkedin.com/in/ ' .
                $slug;


            $fallbackResponse =
                $webSearchService->search(
                    $fallbackQuery
                );


            $fallbackResults =
                $fallbackResponse['organic_results'] ?? [];


            foreach ($fallbackResults as $result) {

                $resultLink =
                    $result['link'] ?? '';


                if (!$resultLink) {
                    continue;
                }


                /*
                 * Extract URL path
                 */

                $resultPath = parse_url(
                    $resultLink,
                    PHP_URL_PATH
                );


                if (!$resultPath) {
                    continue;
                }


                /*
                 * Extract profile slug
                 */

                $resultSlug = trim(
                    preg_replace(
                        '#^/in/#i',
                        '',
                        $resultPath
                    ),
                    '/'
                );


                /*
                 * Compare profile slug
                 */

                if (
                    strtolower($resultSlug) ===
                    strtolower($slug)
                ) {

                    $matchedProfile =
                        $result;

                    break;
                }
            }
        }


        /*
         * ============================================================
         * STEP 1.8
         * No exact profile found
         * ============================================================
         */

        if (!$matchedProfile) {

            throw new \Exception(
                'No exact LinkedIn profile match found.'
            );
        }


        /*
         * ============================================================
         * STEP 1.9
         * Extract person information using Groq
         *
         * AI CALL #1
         * ============================================================
         */

        $person =
            $aiService->extractPersonInfo(
                $matchedProfile['title'] ?? '',
                $matchedProfile['snippet'] ?? ''
            );


        /*
         * ============================================================
         * STEP 1.10
         * Return Step 1 data
         * ============================================================
         */

        return [

            'input_url' =>
                $linkedinUrl,

            'input_slug' =>
                $slug,

            'search_query' =>
                $searchQuery,

            'matched_profile' => [

                'title' =>
                    $matchedProfile['title'] ?? null,

                'link' =>
                    $matchedProfile['link'] ?? null,

                'snippet' =>
                    $matchedProfile['snippet'] ?? null,

                'source' =>
                    $matchedProfile['source'] ?? null,
            ],

            'person' => [

                'name' =>
                    $person['name'] ?? null,

                'headline' =>
                    $person['headline'] ?? null,

                'company' =>
                    $person['company'] ?? null,

                'education' =>
                    $person['education'] ?? null,

                'location' =>
                    $person['location'] ?? null,

                'other_details' =>
                    $person['other_details'] ?? [],
            ],
        ];
    }
}