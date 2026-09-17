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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .container {
            width: 100%;
            max-width: 520px;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin: 0 0 10px;
            text-align: center;
            font-size: 28px;
            color: #1f2937;
        }

        .description {
            margin-bottom: 30px;
            text-align: center;
            color: #6b7280;
            line-height: 1.5;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

    </style>
</head>

<body>

    <div class="container">
        <div class="card">

            <h1>Profile Diagnostic</h1>

            <p class="description">
                Research and analyze a public LinkedIn profile using verified sources.
            </p>

            <form method="POST" action="{{ route('profile.analyze') }}">

                @csrf

                <label for="linkedin_url">LinkedIn Profile URL</label>

                <input type="url" id="linkedin_url" name="linkedin_url" placeholder="https://www.linkedin.com/in/example" required>

                <button type="submit">
                    Analyze Profile
                </button>

            </form>

        </div>
    </div>

</body>
</html>
