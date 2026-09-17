<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Profile Diagnostic</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.5;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 17px;
            margin-top: 25px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 12px;
        }

        .label {
            font-weight: bold;
        }

        .claim {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        .small {
            font-size: 10px;
            color: #666;
        }

        a {
            color: #222;
        }
    </style>
</head>

<body>

    <h1>Profile Diagnostic</h1>

    <div class="subtitle">
        Public web research and profile analysis
    </div>


    {{-- PROFILE --}}

    <h2>Profile Identified</h2>

    @php
        $person = $step1['person'] ?? [];
        $matchedProfile = $step1['matched_profile'] ?? [];
    @endphp

    <div class="card">

        <p>
            <span class="label">Name:</span>
            {{ $person['name'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">Headline:</span>
            {{ $person['headline'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">Company:</span>
            {{ $person['company'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">Education:</span>
            {{ $person['education'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">Location:</span>
            {{ $person['location'] ?? 'Not available' }}
        </p>

    </div>


    {{-- INPUT --}}

    <h2>Analysis Input</h2>

    <div class="card">

        <p>
            <span class="label">LinkedIn URL:</span>
            {{ $step1['input_url'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">Profile Slug:</span>
            {{ $step1['input_slug'] ?? 'Not available' }}
        </p>

    </div>


    {{-- MATCHED PROFILE --}}

    <h2>Matched Profile Source</h2>

    <div class="card">

        <p>
            <span class="label">Title:</span>
            {{ $matchedProfile['title'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">Source:</span>
            {{ $matchedProfile['source'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">URL:</span>
            {{ $matchedProfile['link'] ?? 'Not available' }}
        </p>

        <p>
            <span class="label">Snippet:</span>
            {{ $matchedProfile['snippet'] ?? 'Not available' }}
        </p>

    </div>


    {{-- RESEARCH --}}

    <h2>Research Queries</h2>

    @php
        $queries = $step2['queries'] ?? [];
    @endphp

    <div class="card">

        @if(count($queries) > 0)

            @foreach($queries as $query)

                <p>
                    {{ $query }}
                </p>

            @endforeach

        @else

            <p>No research queries available.</p>

        @endif

    </div>


    {{-- RELEVANT EVIDENCE --}}

    <h2>Relevant Evidence</h2>

    @php
        $relevantResults = $step3['relevant_results'] ?? [];
    @endphp

    @if(count($relevantResults) > 0)

        @foreach($relevantResults as $result)

            <div class="card">

                <h3>
                    {{ $result['title'] ?? 'Untitled Result' }}
                </h3>

                <p>
                    {{ $result['snippet'] ?? 'No snippet available.' }}
                </p>

                <p class="small">
                    {{ $result['link'] ?? '' }}
                </p>

            </div>

        @endforeach

    @else

        <div class="card">
            No relevant evidence was returned.
        </div>

    @endif


    {{-- CLAIMS --}}

    <h2>Extracted Claims</h2>

    @php
        $claims = $step3['claims'] ?? [];
    @endphp

    @if(count($claims) > 0)

        @foreach($claims as $claim)

            <div class="claim">

                @if(is_array($claim))

                    <p>
                        <span class="label">Claim:</span>
                        {{ $claim['claim'] ?? 'Not available' }}
                    </p>

                    <p>
                        <span class="label">Source:</span>
                        {{ $claim['source'] ?? 'Not available' }}
                    </p>

                    <p>
                        <span class="label">Evidence:</span>
                        {{ $claim['evidence'] ?? 'Not available' }}
                    </p>

                    @if(!empty($claim['source_url']))
                        <p class="small">
                            {{ $claim['source_url'] }}
                        </p>
                    @endif

                @else

                    {{ $claim }}

                @endif

            </div>

        @endforeach

    @else

        <div class="card">
            No claims were extracted.
        </div>

    @endif


    {{-- VERIFICATION --}}

    <h2>Evidence Verification</h2>

    @php
        $verification = $step4['verification'] ?? [];
    @endphp

    @if(count($verification) > 0)

        @foreach($verification as $item)

            <div class="claim">

                @if(is_array($item))

                    <p>
                        <span class="label">Claim:</span>
                        {{ $item['claim'] ?? 'Not available' }}
                    </p>

                    @if(!empty($item['status']))
                        <p>
                            <span class="label">Status:</span>
                            {{ ucfirst($item['status']) }}
                        </p>
                    @endif

                    @if(!empty($item['evidence']))
                        <p>
                            <span class="label">Evidence:</span>
                            {{ $item['evidence'] }}
                        </p>
                    @endif

                @else

                    {{ $item }}

                @endif

            </div>

        @endforeach

    @else

        <div class="card">
            Verification data not available.
        </div>

    @endif


    <p class="small">
        Generated by Profile Diagnostic.
    </p>

</body>
</html>