# SEOIndexer

SEOIndexer is a PHP-based tool built with Laravel to help website owners and developers improve search engine indexing. It fetches URLs from WordPress sitemaps, submits them to IndexNow-compatible search engines (like Bing, Yandex, and others), and pings services like Ping-O-Matic, Blo.gs, and Twingly to ensure your content is quickly discovered and indexed.

## Features
- **Fetch Sitemaps**: Automatically retrieves URLs from WordPress sitemaps and saves them as JSON files for each domain.
- **Submit to IndexNow**: Sends URLs one-by-one to multiple IndexNow-compatible search engines, including Bing, Yandex, Naver, Seznam, Yep, and IndexNow Official.
- **Ping Services**: Notifies multiple services (e.g., Ping-O-Matic, Blo.gs, Twingly) about new or updated content by pinging the base domain with the site name from config/indexnow.php using the XML-RPC `weblogUpdates.ping` method.
- **Configurable**: Uses a configuration file to manage multiple websites, their sitemaps, IndexNow keys, and ping services.
- **Error Handling**: Includes robust error handling for HTTP requests, XML parsing, and URL validation, with detailed console messages to help monitor progress.

## Requirements
To use SEOIndexer, ensure your system meets these requirements:
- **PHP**: Version 8.0 or higher
- **Laravel**: Version 8.x or higher
- **Guzzle HTTP Client**: Included with Laravel for making HTTP requests
- **Storage Configuration**: Laravel's `public` disk must be configured for storing JSON files

## Installation
Follow these step-by-step instructions to set up SEOIndexer on your system:

1. **Clone the Repository**  
   Open your terminal (or command prompt) and run the following command to download the project:
   ```bash
   git clone https://github.com/iamsajidjaved/SEOIndexer
   cd SEOIndexer
   ```

2. **Install Dependencies**  
   Install the required PHP packages using Composer (ensure Composer is installed on your system):
   ```bash
   composer install
   ```

3. **Set Up the Environment File**  
   Laravel uses a `.env` file to configure settings. If the `.env` file doesn't exist, copy the example file:
   ```bash
   cp .env.example .env
   ```
   Open the `.env` file in a text editor and ensure the `FILESYSTEM_DISK` is set to `public`:
   ```
   FILESYSTEM_DISK=public
   ```
   If you make changes, save the file.

4. **Generate Application Key**  
   Run the following command to generate a unique application key for Laravel:
   ```bash
   php artisan key:generate
   ```

5. **Set Up the Configuration File**  
   The project requires a `config/indexnow.php` file to specify your websites, sitemaps, IndexNow keys, and ping services. Follow these steps:
   - **Check if `config/indexnow.php` Exists**: Navigate to the `config` directory in your project folder (`SEOIndexer/config`). If `indexnow.php` exists, proceed to edit it. If it doesn't exist, create it:
     ```bash
     touch config/indexnow.php
     ```
   - **Edit or Add Content**: Open `config/indexnow.php` in a text editor and add your site details. Below is an example configuration:
     ```php
     <?php

     return [
         [
             'name'         => 'Example Blog',
             'domain'       => 'www.example.com',
             'feed_url'     => 'https://www.example.com/feed/',
             'indexnow_key' => 'your-indexnow-key',
             'sitemaps'     => [
                 'https://www.example.com/post-sitemap.xml',
                 'https://www.example.com/page-sitemap.xml',
                 'https://www.example.com/category-sitemap.xml',
             ],
             'services'     => [
                'Ping-O-Matic' => 'http://rpc.pingomatic.com/',
                'Blo.gs'       => 'http://ping.blo.gs/',
                'Twingly'      => 'http://rpc.twingly.com/',
                'Bloggers.jp'  => 'http://ping.bloggers.jp/rpc/',
                'With2.net'    => 'http://blog.with2.net/ping.php',
                'Blo.gs (alt)' => 'http://blo.gs/ping.php',
                'BlogShares'   => 'http://www.blogshares.com/rpc.php',
                'FC2'          => 'http://ping.fc2.com',
                'Pingoat'      => 'http://pingoat.com/goat/RPC2',
                'Weblogues'    => 'http://weblogues.com/RPC',
                'Bloggerei'    => 'http://rpc.bloggerei.de/ping/',
            ],
         ],
     ];
     ```
   - **Obtain an IndexNow Key**: For detailed instructions on generating an IndexNow key, visit [Bing's IndexNow Get Started Guide](https://www.bing.com/indexnow/getstarted).
   - **Notes**:
     - Replace `www.example.com` with your website's domain (e.g., `8xbet.hot`).
     - Replace `your-indexnow-key` with the key obtained from Bing or another IndexNow provider.
     - Set `name` to a descriptive name for the site (used in ping requests).
     - Set `feed_url` to the site’s RSS feed URL (used by other commands, not required for pinging).
     - List sitemap URLs in `sitemaps` for fetching URLs.
     - Specify ping services in `services` (e.g., Ping-O-Matic, Blo.gs, Twingly) with their XML-RPC endpoints.
     - Add multiple site configurations in the array if you manage multiple websites.

## Usage
SEOIndexer provides three main console commands to fetch URLs, submit them to IndexNow, and ping configured services. Run these commands in your terminal from the project root directory.

### 1. Fetch Sitemaps
This command retrieves URLs from the sitemaps listed in `config/indexnow.php` and saves them as JSON files:
```bash
php artisan fetch:sitemaps
```
- **What Happens**: The command fetches URLs from each sitemap, removes duplicates, and stores them in `storage/app/public/indexnow/{domain}.json` (e.g., `storage/app/public/indexnow/8xbet.hot.json`).
- **Output**: The terminal shows progress, including the number of URLs fetched and any errors encountered.

### 2. Submit URLs to IndexNow
This command submits the URLs stored in the JSON files to IndexNow-compatible search engines:
```bash
php artisan submit:indexnow
```
- **What Happens**: The command reads the JSON files, validates each URL, and sends them individually to search engines like Bing, Yandex, Naver, and others using the `indexnow_key` from the configuration.
- **Output**: The terminal displays success or failure messages for each URL submission, helping you track the process.

### 3. Ping Services
This command notifies configured services (e.g., Ping-O-Matic, Blo.gs, Twingly) about new or updated content by pinging the base domain with the site name from `config/indexnow.php`:
```bash
php artisan ping:services
```
- **What Happens**: The command sends an XML-RPC `weblogUpdates.ping` request to each service listed in `config/indexnow.php` (e.g., `http://rpc.pingomatic.com/`) using the base domain (e.g., `https://8xbet.hot`) and site name (e.g., `8xBet`).
- **Output**: The terminal shows progress for each domain and service, including HTTP status, headers, response length, raw response, and success or failure messages.

## Commands
- **`fetch:sitemaps`**: Fetches URLs from sitemaps listed in `config/indexnow.php` and saves them to JSON files in the `storage/app/public/indexnow` directory.
- **`submit:indexnow`**: Submits URLs from the JSON files to IndexNow search engines using individual GET requests with the `indexnow_key`.
- **`ping:services`**: Pings configured services (e.g., Ping-O-Matic, Blo.gs, Twingly) with the base domain and site name from `config/indexnow.php` using the XML-RPC `weblogUpdates.ping` method.

## File Structure
Key files in the project:
- `app/Console/Commands/FetchSitemaps.php`: Handles fetching and parsing sitemaps to extract URLs.
- `app/Console/Commands/SubmitToIndexNow.php`: Manages submitting URLs to IndexNow endpoints.
- `app/Console/Commands/PingServices.php`: Sends pings to configured services for content updates.
- `config/indexnow.php`: Configuration file where you define site domains, sitemaps, IndexNow keys, and ping services.

## Notes
- **Storage Configuration**: Ensure the `public` disk is properly set up in `config/filesystems.php`. The default configuration should work, but verify that the `public` disk points to `storage/app/public`.
- **Supported Search Engines**: The IndexNow submission supports Bing, Yandex, Naver, Seznam, Yep, and IndexNow Official.
- **Ping Services**: The `ping:services` command supports services defined in `config/indexnow.php` (e.g., Ping-O-Matic, Blo.gs, Twingly) using the XML-RPC `weblogUpdates.ping` method, pinging only the base domain with the site name.
- **URL Validation**: The base domain and feed URL are validated before pinging to prevent errors.
- **Console Feedback**: Commands provide detailed logs, including HTTP status, headers, response length, and raw responses, to help monitor progress.

## TODO
Future enhancements planned for SEOIndexer:
- Integrate Google Indexer API to support Google’s indexing services.
- ~~Add Ping-O-Matic integration for pinging services to notify search engines and aggregators of new content.~~ [Completed]

## Contributing
Want to contribute to SEOIndexer? Follow these steps:
1. Fork the repository on GitHub.
2. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature
   ```
3. Commit your changes with a clear message:
   ```bash
   git commit -m 'Add your feature'
   ```
4. Push to your branch:
   ```bash
   git push origin feature/your-feature
   ```
5. Open a pull request on GitHub.

## Support
For any issues or questions, contact the developer listed in the Footer section below.

## License
This project is licensed under the MIT License.

## Footer
Developed by **Sajid Javed**, an automation expert and SEO specialist with deep knowledge of advanced and secret techniques in search engine optimization. For support or work inquiries, contact:
- **Email**: engr.maliksajidkhan@gmail.com
- **WhatsApp**: +971503973612