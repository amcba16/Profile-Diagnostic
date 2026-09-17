<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    /**
     * ============================================================
     * STEP 1
     * Extract person's professional identity
     * ============================================================
     */
    public function extractPersonInfo(
        string $title,
        string $snippet
    ): array {
        $prompt = <<<PROMPT
Extract useful identity information from this public LinkedIn search result.

Title:
$title

Snippet:
$snippet

Return ONLY valid JSON in this exact structure:

{
    "name": null,
    "headline": null,
    "company": null,
    "education": null,
    "location": null,
    "other_details": []
}

Rules:

1. Do not guess.
2. Only use information explicitly present in the title or snippet.
3. Preserve multiple pieces of information.
4. If the snippet contains multiple professional titles such as
   "PHP Developer | Computer Science Engineer | Freelancer",
   keep all of them in "headline".
5. Put education/institution information in "education".
6. Put useful information that does not fit other fields into "other_details".
7. Use null when information is not available.
8. Do not verify or judge the information.
9. Do not add explanations.
PROMPT;

        $response = $this->groqRequest(
            $prompt,
            700
        );

        return $this->parseJsonResponse(
            $response,
            [
                'name' => null,
                'headline' => null,
                'company' => null,
                'education' => null,
                'location' => null,
                'other_details' => [],
            ]
        );
    }


    /**
     * ============================================================
     * STEP 3
     *
     * Combined AI call:
     *
     * 1. Identify relevant research results
     * 2. Extract factual claims from those results
     *
     * This replaces:
     *
     * filterRelevantResults()
     * +
     * extractClaims()
     *
     * with ONE Groq request.
     * ============================================================
     */
    public function analyzeRelevantResults(
        array $person,
        array $researchResults
    ): array {
        if (empty($researchResults)) {
            return [
                'relevant_indexes' => [],
                'claims' => [],
            ];
        }

        $name = $person['name'] ?? '';
        $headline = $person['headline'] ?? '';
        $company = $person['company'] ?? '';
        $education = $person['education'] ?? '';
        $location = $person['location'] ?? '';

        $prompt = <<<PROMPT
You are analyzing public web search results about ONE specific person.

TARGET PERSON:

Name: $name
Headline: $headline
Company: $company
Education: $education
Location: $location

Your task has TWO parts.

PART 1:
Identify which search results are actually about the target person.

PART 2:
Extract factual claims only from the relevant results.

IMPORTANT RULES FOR RELEVANCE:

1. Return only results that are reasonably supported as belonging to the target person.
2. Do not guess.
3. Do not infer identity without evidence.
4. Use name, company, professional role, education and location to distinguish people.
5. Exclude results about different people with the same or similar name.
6. LinkedIn results matching the target person's profile can be relevant.
7. Directory results can be relevant when they clearly identify the same person.
8. If there is not enough evidence to associate a result with the target person, exclude it.

IMPORTANT RULES FOR CLAIMS:

1. Extract only facts explicitly present in the supplied result.
2. Do not guess or infer.
3. Each claim must come from ONE search result.
4. Include the source URL.
5. Include the source name.
6. Include the supporting evidence text.
7. Ignore results that contain no useful factual information.
8. Do not verify claims.
9. Do not create information that is not present in the result.
10. Keep claims concise.
11. Do not create duplicate claims.

Return ONLY valid JSON in exactly this structure:

{
    "relevant_indexes": [0, 2, 5],
    "claims": [
        {
            "claim": "The factual claim.",
            "source_url": "https://example.com",
            "source": "Example",
            "evidence": "Supporting text from the search result."
        }
    ]
}

RESEARCH RESULTS:
PROMPT;

        foreach ($researchResults as $index => $result) {

            $prompt .= "\n\n";

            $prompt .= "RESULT INDEX: {$index}\n";

            $prompt .= "TITLE: "
                . ($result['title'] ?? '')
                . "\n";

            $prompt .= "URL: "
                . ($result['link'] ?? '')
                . "\n";

            $prompt .= "SNIPPET: "
                . ($result['snippet'] ?? '')
                . "\n";

            $prompt .= "SOURCE: "
                . ($result['source'] ?? '')
                . "\n";

            $prompt .= "END RESULT\n";
        }


        /**
         * One Groq call only.
         */
        $response = $this->groqRequest(
            $prompt,
            1800
        );

        $data = $this->parseJsonResponse(
            $response,
            [
                'relevant_indexes' => [],
                'claims' => [],
            ]
        );


        /**
         * Validate relevant indexes.
         */
        $relevantIndexes =
            $data['relevant_indexes'] ?? [];

        if (!is_array($relevantIndexes)) {
            $relevantIndexes = [];
        }

        $relevantIndexes = array_map(
            'intval',
            $relevantIndexes
        );

        $relevantIndexes = array_values(
            array_unique($relevantIndexes)
        );


        /**
         * Make sure AI cannot return
         * indexes that do not exist.
         */
        $validIndexes =
            array_keys($researchResults);

        $relevantIndexes = array_values(
            array_intersect(
                $relevantIndexes,
                $validIndexes
            )
        );


        /**
         * Validate claims.
         */
        $claims = $data['claims'] ?? [];

        if (!is_array($claims)) {
            $claims = [];
        }


        return [
            'relevant_indexes' => $relevantIndexes,
            'claims' => $claims,
        ];
    }


    /**
     * ============================================================
     * STEP 4
     *
     * Verify all extracted claims in ONE AI request.
     * ============================================================
     */
    public function verifyClaimsEvidence(
        array $claims,
        array $person,
        array $evidenceResults
    ): array {
        /**
         * Nothing to verify.
         */
        if (empty($claims)) {
            return [];
        }


        $personJson = json_encode(
            $person,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES
        );

        $claimsJson = json_encode(
            $claims,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES
        );

        $evidenceJson = json_encode(
            $evidenceResults,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES
        );


        $prompt = <<<PROMPT
You are verifying factual claims about a person using public web evidence.

PERSON:

$personJson

CLAIMS:

$claimsJson

EVIDENCE FOUND FROM PUBLIC WEB SEARCHES:

$evidenceJson

For EACH claim, evaluate whether the provided evidence supports it.

Return ONLY valid JSON in exactly this structure:

{
    "verifications": [
        {
            "claim": "...",
            "status": "Verified",
            "confidence": "high",
            "evidence": [
                {
                    "url": "...",
                    "source": "...",
                    "source_type": "primary",
                    "supports_claim": true,
                    "reason": "..."
                }
            ],
            "reason": "..."
        }
    ]
}

RULES:

1. Verify every input claim.
2. Do not guess.
3. Use ONLY the provided evidence.
4. Do not treat search-engine ranking as evidence.
5. Prefer primary sources.
6. The person's own LinkedIn profile is supporting evidence but should NOT automatically make a claim Verified.
7. Third-party directories and aggregators are weaker evidence.
8. Multiple websites repeating the same information do not automatically count as independent verification.
9. Look for evidence that directly supports the exact claim.
10. Consider evidence that contradicts the claim.
11. Status must be exactly one of:
    - Verified
    - Partially Verified
    - Unverified
12. Use Verified only when there is strong evidence supporting the claim.
13. Use Partially Verified when there is credible supporting evidence but insufficient evidence for full verification.
14. Use Unverified when there is insufficient or contradictory evidence.
15. Confidence must be exactly one of:
    - high
    - medium
    - low
16. Do not create evidence or URLs.
17. Every evidence URL must exist in the provided evidence.
18. If no evidence supports a claim, return an empty evidence array.
19. Return exactly one verification object for every input claim.
20. Keep the response concise.
21. Do not add explanations outside the JSON.
PROMPT;


        /**
         * One Groq call.
         */
        $response = $this->groqRequest(
            $prompt,
            1500
        );


        $data = $this->parseJsonResponse(
            $response,
            [
                'verifications' => [],
            ]
        );


        if (
            !isset($data['verifications']) ||
            !is_array($data['verifications'])
        ) {
            return [];
        }


        return $data['verifications'];
    }


    /**
     * ============================================================
     * GROQ REQUEST
     * ============================================================
     *
     * $maxTokens allows each operation to use
     * a smaller completion limit.
     * ============================================================
     */
    private function groqRequest(
        string $prompt,
        int $maxTokens = 1500
    ): string {

        $response = Http::timeout(120)
            ->withToken(
                env('GROQ_API_KEY')
            )
            ->post(
                'https://api.groq.com/openai/v1/chat/completions',
                [
                    'model' => 'openai/gpt-oss-20b',

                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],

                    'temperature' => 0,

                    'max_completion_tokens' =>
                        $maxTokens,

                    'reasoning_effort' => 'low',
                ]
            );


        if ($response->failed()) {

            throw new \Exception(
                'Groq API request failed: ' .
                $response->body()
            );
        }


        $content = $response->json(
            'choices.0.message.content'
        );


        if (!$content) {

            throw new \Exception(
                'Groq returned an empty response.'
            );
        }


        return $content;
    }


    /**
     * ============================================================
     * PARSE JSON RESPONSE
     * ============================================================
     */
    private function parseJsonResponse(
        string $text,
        array $default = []
    ): array {

        $text = trim($text);


        /**
         * Remove markdown code fences.
         */
        $text = preg_replace(
            '/^```json\s*/i',
            '',
            $text
        );

        $text = preg_replace(
            '/^```\s*/i',
            '',
            $text
        );

        $text = preg_replace(
            '/\s*```$/',
            '',
            $text
        );

        $text = trim($text);


        /**
         * Try direct JSON parsing.
         */
        $decoded = json_decode(
            $text,
            true
        );

        if (is_array($decoded)) {
            return $decoded;
        }


        /**
         * Try extracting JSON object
         * from surrounding text.
         */
        $start = strpos(
            $text,
            '{'
        );

        $end = strrpos(
            $text,
            '}'
        );


        if (
            $start !== false &&
            $end !== false &&
            $end > $start
        ) {

            $json = substr(
                $text,
                $start,
                $end - $start + 1
            );

            $decoded = json_decode(
                $json,
                true
            );

            if (is_array($decoded)) {
                return $decoded;
            }
        }


        return $default;
    }


    /**
     * ============================================================
     * COMPATIBILITY METHOD
     *
     * Kept in case another part of the application
     * still uses processResearchFile().
     * ============================================================
     */
    public function processResearchFile(
        string $filePath
    ): array {

        if (!file_exists($filePath)) {

            throw new \Exception(
                'Research JSON file not found: ' .
                $filePath
            );
        }


        $json = file_get_contents(
            $filePath
        );


        $data = json_decode(
            $json,
            true
        );


        if (!is_array($data)) {

            throw new \Exception(
                'Invalid JSON in research file.'
            );
        }


        return $data;
    }
}