Growpido – Track B
Prospect to Diagnostic
Developer README / Setup Guide
1. Project Overview
This Laravel application implements Track B – Prospect to Diagnostic. It accepts a public LinkedIn profile URL,
identifies the profile using public web search, researches the person using public sources, filters relevant research
results, extracts factual claims, and attempts to verify those claims using AI-generated evidence analysis. The
application also provides a web results page and PDF export.
2. Requirements
Install the following before running the project: PHP 8.2+ (PHP 8.3 recommended), Composer, Laravel, MySQL, a web
server or Laravel's built-in server, a SerpApi account/API key, and a Groq API key.
3. Clone and Install
git clone <YOUR_GITHUB_REPOSITORY_URL>
cd growpido-diagnostic
composer install
copy .env.example .env
php artisan key:generate
4. Configure SerpApi and Groq Keys
Open the project's .env file and add the API keys. Never commit .env or expose the keys in GitHub.
SERPAPI_KEY=your_serpapi_api_key
GROQ_API_KEY=your_groq_api_key
Use the exact environment variable names expected by the project's service classes. If the existing code uses a
different key name, keep the existing name rather than creating a second variable.
After changing .env, clear Laravel's cached configuration:
php artisan optimize:clear
5. MySQL Database Setup
Connect the application to a MySQL database. Create an empty database first, then update the database values in
.env.
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=growpido_diagnostic
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
Then run the Laravel migrations:
php artisan migrate
If the project contains seeders and they are required for local testing, run: php artisan db:seed. Do not use
migrate:fresh on a database containing data unless you intentionally want to delete all tables.
6. Run the Application
php artisan serve
Open the local URL shown by Laravel, normally http://127.0.0.1:8000. Enter a public LinkedIn profile URL and click
Analyze Profile.
7. Main Application Flow
• Step 1 – LinkedIn profile identification: validates the URL, extracts the profile slug, searches SerpApi, and matches
the exact LinkedIn profile slug.
• Step 2 – Public web research: searches for public information about the identified person.
• Step 3 – Relevance and claim extraction: uses Groq to identify relevant results and extract factual claims.
• Step 4 – Evidence verification: sends claims and relevant evidence to Groq for verification.
• PDF export – the results page can export the diagnostic as a PDF.
8. Important Configuration / Troubleshooting
The AI verification stage can fail or return incomplete data if the Groq model reaches its rate/token limit.
the rate limit window to reset.
If API requests fail, verify the .env keys, run php artisan optimize:clear, and confirm the API services are reachable.
9. Security Notes
Keep API keys only in .env. Do not commit .env to Git. Use .env.example with placeholder values. Only public
information should be researched for this assignment; do not bypass logins or access private data.
10. Suggested .env.example
APP_NAME="Growpido Diagnostic"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
SERPAPI_KEY=
GROQ_API_KEY=
11. Useful Laravel Commands
composer install
php artisan key:generate
php artisan optimize:clear
php artisan migrate
php artisan route:list
php artisan serve
12. Notes for the Next Developer
The application is intentionally simple and uses Laravel controllers, services, Blade views, SerpApi for public web
search, and Groq for AI processing. Before extending the project, review the existing AiService, WebSearchService,
ProfileDiagnosticController, routes/web.php, and the Blade views. Avoid committing API credentials.