<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchSitemaps extends Command
{
    protected $signature   = 'fetch:sitemaps';
    protected $description = 'Fetch all URLs from WordPress sitemaps and store them per domain';

    public function handle()
    {
        $sites = config('indexnow');

        foreach ($sites as $site) {
            $domain   = $site['domain'];
            $sitemaps = $site['sitemaps'];
            $this->info("🔍 Fetching sitemaps for: $domain");

            $urls = [];

            foreach ($sitemaps as $sitemapUrl) {
                $this->info($sitemapUrl);
                $urls = array_merge($urls, $this->parseSitemap($sitemapUrl));
            }

            $urls = array_unique($urls);
            Storage::put("indexnow/$domain.json", json_encode($urls, JSON_PRETTY_PRINT));
            $this->info("✅ Stored " . count($urls) . " URLs for $domain.");
        }
    }

    private function parseSitemap($url)
    {
        $results = [];

        try {
            $response = Http::timeout(10)->get($url);
            if (! $response->ok()) {
                return [];
            }

            $xml = simplexml_load_string($response->body());

            if (isset($xml->sitemap)) {
                foreach ($xml->sitemap as $sitemap) {
                    $results = array_merge($results, $this->parseSitemap((string) $sitemap->loc));
                }
            } elseif (isset($xml->url)) {
                foreach ($xml->url as $urlNode) {
                    $results[] = (string) $urlNode->loc;
                }
            }
        } catch (\Throwable $e) {
            $this->error("❌ Failed to parse: $url — " . $e->getMessage());
        }

        return $results;
    }
}
