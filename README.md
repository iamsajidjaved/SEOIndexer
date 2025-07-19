# Laravel IndexNow Sitemap Fetcher and Submitter

This Laravel project provides two console commands to fetch URLs from WordPress sitemaps and submit them to IndexNow-compatible search engines for faster indexing.

## Features
- **Fetch Sitemaps**: Retrieves URLs from specified WordPress sitemaps and stores them in JSON files per domain.
- **Submit to IndexNow**: Submits URLs individually to multiple IndexNow-compatible search engines (e.g., Bing, Yandex, Naver, Seznam, Yep, IndexNow Official).
- **Configurable**: Uses a configuration file to manage multiple sites, their sitemaps, and IndexNow API keys.
- **Error Handling**: Robust error handling for HTTP requests and XML parsing, with detailed console feedback.

## Requirements
- PHP >= 8.0
- Laravel >= 8.x
- Guzzle HTTP Client (included with Laravel)
- Public disk storage configured in Laravel

## Installation
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd <repository-folder>
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Configure the `.env` file with storage settings if needed.
4. Set up the `config/indexnow.php` file with your site details (see Configuration section).

## Configuration
Edit `config/indexnow.php` to include your sites, their sitemaps, and IndexNow keys. Example:
```php
return [
    [
        'domain' => 'www.example.com',
        'indexnow_key' => 'your-indexnow-key',
        'sitemaps' => [
            'https://www.example.com/post-sitemap.xml',
            'https://www.example.com/page-sitemap.xml',
        ],
    ],
];
```

## Usage
### 1. Fetch Sitemaps
Run the following command to fetch URLs from the configured sitemaps and store them in JSON files:
```bash
php artisan indexnow:fetch-sitemaps
```
- Output: JSON files stored in `storage/app/public/indexnow/{domain}.json`.

### 2. Submit URLs to IndexNow
Submit the fetched URLs to IndexNow-compatible search engines:
```bash
php artisan indexnow:submit
```
- Output: Console logs indicating success or failure for each URL submission per search engine.

## Commands
- **`indexnow:fetch-sitemaps`**: Fetches URLs from sitemaps and saves them to JSON files.
- **`indexnow:submit`**: Submits URLs from JSON files to IndexNow search engines via individual GET requests.

## File Structure
- `app/Console/Commands/FetchSitemaps.php`: Handles sitemap fetching and URL extraction.
- `app/Console/Commands/SubmitToIndexNow.php`: Manages URL submission to IndexNow endpoints.
- `config/indexnow.php`: Configuration file for site domains, sitemaps, and API keys.

## Notes
- Ensure the `public` disk is properly configured in `config/filesystems.php` for storing JSON files.
- The project supports multiple search engines for IndexNow submissions, including Bing, Yandex, Naver, Seznam, Yep, and IndexNow Official.
- URLs are validated before submission to ensure they are properly formatted.
- The commands include detailed console feedback for debugging and monitoring.

## Contributing
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -m 'Add your feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

## License
This project is licensed under the MIT License.