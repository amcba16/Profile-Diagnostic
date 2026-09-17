<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profile Diagnostic</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            width: min(1200px, 92%);
            margin: 40px auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 30px;
            color: #111827;
        }

        .header p {
            margin: 7px 0 0;
            color: #6b7280;
        }

        .new-analysis {
            background: #2563eb;
            color: white;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .new-analysis:hover {
            background: #1d4ed8;
            text-decoration: none;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
        }

        .card-title {
            margin: 0 0 18px;
            font-size: 20px;
            color: #111827;
        }

        .profile-header {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .avatar {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .profile-name {
            margin: 0;
            font-size: 25px;
            color: #111827;
        }

        .headline {
            margin: 7px 0;
            color: #4b5563;
        }

        .company {
            color: #2563eb;
            font-weight: 600;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 22px;
        }

        .stat {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .stat-number {
            font-size: 27px;
            font-weight: bold;
            color: #2563eb;
        }

        .stat-label {
            margin-top: 5px;
            color: #6b7280;
            font-size: 14px;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail {
            background: #f9fafb;
            border-radius: 9px;
            padding: 15px;
        }

        .detail-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: #111827;
        }

        .query {
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #fafafa;
            font-family: monospace;
            word-break: break-word;
        }

        .result {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 17px;
            margin-bottom: 14px;
        }

        .result-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .result-snippet {
            color: #4b5563;
            line-height: 1.5;
            margin: 8px 0;
        }

        .source {
            font-size: 13px;
            color: #6b7280;
        }

        .claim {
            border-left: 4px solid #2563eb;
            background: #f8fafc;
            padding: 16px;
            margin-bottom: 14px;
            border-radius: 7px;
        }

        .claim-text {
            font-weight: 600;
            line-height: 1.5;
        }

        .claim-source {
            margin-top: 10px;
            font-size: 13px;
            color: #6b7280;
        }

        .evidence {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 6px;
            color: #4b5563;
            font-size: 14px;
        }

        .verification {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .verification-status {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 9px;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .empty {
            padding: 25px;
            text-align: center;
            background: #f9fafb;
            border-radius: 9px;
            color: #6b7280;
        }

        .empty-title {
            font-weight: 700;
            color: #374151;
            margin-bottom: 5px;
        }

        .profile-link {
            display: inline-block;
            margin-top: 10px;
            padding: 9px 14px;
            background: #eff6ff;
            border-radius: 7px;
            font-weight: 600;
        }

        .url {
            word-break: break-all;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-actions form {
            margin: 0;
        }

        .export-pdf {
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            background: #111827;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .export-pdf:hover {
            opacity: 0.9;
        }
        @media (max-width: 800px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .details {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 500px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .profile-header {
                align-items: flex-start;
            }

            .profile-name {
                font-size: 21px;
            }
        }

    </style>
</head>

<body>

    @php

    /*
    * ============================================================
    * Prepare data
    * ============================================================
    */

    $person = $step1['person'] ?? [];

    $name = $person['name'] ?? 'Unknown Person';

    $headline = $person['headline']
    ?? 'No headline available';

    $company = $person['company']
    ?? 'Not available';

    $education = $person['education']
    ?? 'Not available';

    $location = $person['location']
    ?? 'Not available';

    $firstLetter = strtoupper(
    substr($name, 0, 1)
    );


    /*
    * STEP 2
    */

    $queries = $step2['queries'] ?? [];

    $allResults = $step2['results'] ?? [];


    /*
    * STEP 3
    */

    $relevantResults =
    $step3['relevant_results'] ?? [];

    $claims =
    $step3['claims'] ?? [];


    /*
    * STEP 4
    */

    $verification =
    $step4['verification'] ?? [];


    /*
    * Matched profile
    */

    $matchedProfile =
    $step1['matched_profile'] ?? [];

    @endphp


    <div class="container">

        <!-- HEADER -->

        <div class="header">

            <div>
                <h1>Profile Diagnostic</h1>

                <p>
                    Public web research and profile analysis
                </p>
            </div>

            <div class="header-actions">

                <a href="{{ route('profile.page') }}" class="new-analysis">
                    + New Analysis
                </a>

                <form method="POST" action="{{ route('profile.export.pdf') }}">
                    @csrf

                    <input type="hidden" name="data" value="{{ json_encode([
                    'success' => $success ?? true,
                    'step1' => $step1 ?? [],
                    'step2' => $step2 ?? [],
                    'step3' => $step3 ?? [],
                    'step4' => $step4 ?? [],
                ]) }}">

                    <button type="submit" class="export-pdf">
                        Export PDF
                    </button>

                </form>

            </div>

        </div>


        <!-- PROFILE -->

        <div class="card">

            <h2 class="card-title">
                Profile Identified
            </h2>

            <div class="profile-header">

                <div class="avatar">
                    {{ $firstLetter }}
                </div>

                <div>

                    <h2 class="profile-name">
                        {{ $name }}
                    </h2>

                    <div class="headline">
                        {{ $headline }}
                    </div>

                    <div class="company">
                        {{ $company }}
                    </div>

                </div>

            </div>

        </div>


        <!-- STATISTICS -->

        <div class="stats">

            <div class="stat">

                <div class="stat-number">
                    {{ count($queries) }}
                </div>

                <div class="stat-label">
                    Search Queries
                </div>

            </div>


            <div class="stat">

                <div class="stat-number">
                    {{ count($allResults) }}
                </div>

                <div class="stat-label">
                    Results Found
                </div>

            </div>


            <div class="stat">

                <div class="stat-number">
                    {{ count($relevantResults) }}
                </div>

                <div class="stat-label">
                    Relevant Sources
                </div>

            </div>


            <div class="stat">

                <div class="stat-number">
                    {{ count($claims) }}
                </div>

                <div class="stat-label">
                    Claims Extracted
                </div>

            </div>

        </div>


        <!-- PROFILE DETAILS -->

        <div class="card">

            <h2 class="card-title">
                Profile Details
            </h2>

            <div class="details">

                <div class="detail">

                    <div class="detail-label">
                        Name
                    </div>

                    <div class="detail-value">
                        {{ $name }}
                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Company
                    </div>

                    <div class="detail-value">
                        {{ $company }}
                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Education
                    </div>

                    <div class="detail-value">
                        {{ $education }}
                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Location
                    </div>

                    <div class="detail-value">
                        {{ $location }}
                    </div>

                </div>

            </div>

        </div>


        <!-- INPUT INFORMATION -->

        <div class="card">

            <h2 class="card-title">
                Analysis Input
            </h2>

            <div class="details">

                <div class="detail">

                    <div class="detail-label">
                        LinkedIn URL
                    </div>

                    <div class="detail-value url">
                        {{ $step1['input_url'] ?? 'Not available' }}
                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        LinkedIn Slug
                    </div>

                    <div class="detail-value">
                        {{ $step1['input_slug'] ?? 'Not available' }}
                    </div>

                </div>


                <div class="detail">

                    <div class="detail-label">
                        Search Query
                    </div>

                    <div class="detail-value">
                        {{ $step1['search_query'] ?? 'Not available' }}
                    </div>

                </div>

            </div>

        </div>


        <!-- MATCHED LINKEDIN PROFILE -->

        <div class="card">

            <h2 class="card-title">
                Matched Profile Source
            </h2>

            @if(!empty($matchedProfile))

            <div class="result">

                <div class="result-title">
                    {{ $matchedProfile['title'] ?? 'LinkedIn Profile' }}
                </div>

                <div class="result-snippet">
                    {{ $matchedProfile['snippet'] ?? 'No snippet available.' }}
                </div>

                <div class="source">
                    {{ $matchedProfile['source'] ?? 'LinkedIn' }}
                </div>

                @if(!empty($matchedProfile['link']))

                <a href="{{ $matchedProfile['link'] }}" target="_blank" rel="noopener noreferrer" class="profile-link">
                    Open LinkedIn Profile
                </a>

                @endif

            </div>

            @else

            <div class="empty">
                Matched profile information is not available.
            </div>

            @endif

        </div>


        <!-- SEARCH QUERIES -->

        <div class="card">

            <h2 class="card-title">
                Research Queries
            </h2>

            @if(count($queries) > 0)

            @foreach($queries as $query)

            <div class="query">
                {{ $query }}
            </div>

            @endforeach

            @else

            <div class="empty">
                <div class="empty-title">
                    No queries found
                </div>

                No research queries were returned.
            </div>

            @endif

        </div>


        <!-- ALL RESEARCH RESULTS -->

        <div class="card">

            <h2 class="card-title">
                Research Activity
            </h2>

            @if(count($allResults) > 0)

            @foreach($allResults as $index => $result)

            <div class="result">

                <div class="result-title">
                    {{ $result['title'] ?? 'Untitled Result' }}
                </div>

                <div class="result-snippet">
                    {{ $result['snippet'] ?? 'No snippet available.' }}
                </div>

                <div class="source">
                    Source:
                    {{ $result['source'] ?? 'Unknown' }}
                </div>

                @if(!empty($result['link']))

                <a href="{{ $result['link'] }}" target="_blank" rel="noopener noreferrer" class="profile-link">
                    View Source
                </a>

                @endif

            </div>

            @endforeach

            @else

            <div class="empty">
                No research results were found.
            </div>

            @endif

        </div>


        <!-- RELEVANT EVIDENCE -->

        <div class="card">

            <h2 class="card-title">
                Relevant Evidence
            </h2>

            @if(count($relevantResults) > 0)

            @foreach($relevantResults as $result)

            <div class="result">

                <div class="result-title">
                    {{ $result['title'] ?? 'Untitled Result' }}
                </div>

                <div class="result-snippet">
                    {{ $result['snippet'] ?? 'No snippet available.' }}
                </div>

                <div class="source">
                    {{ $result['source'] ?? 'Unknown source' }}
                </div>

                @if(!empty($result['link']))

                <a href="{{ $result['link'] }}" target="_blank" rel="noopener noreferrer" class="profile-link">
                    Open Evidence
                </a>

                @endif

            </div>

            @endforeach

            @else

            <div class="empty">
                <div class="empty-title">
                    No relevant evidence
                </div>

                No relevant research sources were identified.
            </div>

            @endif

        </div>


        <!-- CLAIMS -->

        <div class="card">

            <h2 class="card-title">
                Extracted Claims
            </h2>

            @if(count($claims) > 0)

            @foreach($claims as $index => $claim)

            <div class="claim">

                <div class="claim-text">
                    {{ $claim['claim'] ?? 'Claim ' . ($index + 1) }}
                </div>

                @if(!empty($claim['source']))

                <div class="claim-source">
                    Source:
                    {{ $claim['source'] }}
                </div>

                @endif

                @if(!empty($claim['source_url']))

                <div class="claim-source">

                    <a href="{{ $claim['source_url'] }}" target="_blank" rel="noopener noreferrer">
                        View Source
                    </a>

                </div>

                @endif

                @if(!empty($claim['evidence']))

                <div class="evidence">

                    <strong>
                        Evidence:
                    </strong>

                    {{ $claim['evidence'] }}

                </div>

                @endif

            </div>

            @endforeach

            @else

            <div class="empty">
                <div class="empty-title">
                    No claims extracted
                </div>

                No factual claims were returned.
            </div>

            @endif

        </div>


        <!-- VERIFICATION -->

        <div class="card">

            <h2 class="card-title">
                Evidence Verification
            </h2>

            @if(count($verification) > 0)

            @foreach($verification as $index => $item)

            <div class="verification">

                @if(is_array($item))

                <div class="claim-text">

                    {{ $item['claim'] ?? 'Claim ' . ($index + 1) }}

                </div>


                @if(!empty($item['status']))

                <span class="verification-status">

                    {{ ucfirst($item['status']) }}

                </span>

                @endif


                @if(!empty($item['evidence']))

                <div class="evidence">

                    <strong>
                        Evidence:
                    </strong>

                    {{ $item['evidence'] }}

                </div>

                @endif

                @else

                <div class="claim-text">
                    {{ $item }}
                </div>

                @endif

            </div>

            @endforeach

            @else

            <div class="empty">

                <div class="empty-title">
                    Verification data not available
                </div>

                No verification results were returned.

            </div>

            @endif

        </div>

    </div>

</body>
</html>
