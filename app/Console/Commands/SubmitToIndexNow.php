<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SubmitToIndexNow extends Command
{
    protected $signature = 'indexnow:submit';
    protected $description = 'Submit URLs from sitemap JSON to IndexNow via individual GET requests';

    protected array $searchEngines = [
        'IndexNow Official' => 'https://api.indexnow.org/indexnow',
        'Bing'              => 'https://www.bing.com/indexnow',
        'Yandex'            => 'https://yandex.com/indexnow',
        'Naver'             => 'https://searchadvisor.naver.com/indexnow',
        'Seznam'            => 'https://search.seznam.cz/indexnow',
        'Yep'               => 'https://indexnow.yep.com/indexnow',
    ];

    public function handle()
    {
        $sites = config('indexnow');

        if (empty($sites) || !is_array($sites)) {
            $this->error('⚠️ No sites found in config/indexnow.php');
            return 1;
        }

        $disk = Storage::disk('public');

        foreach ($sites as $site) {
            $domain = $site['domain'] ?? null;
            $indexNowKey = $site['indexnow_key'] ?? null;

            if (!$domain || !$indexNowKey) {
                $this->error('❌ Missing domain or indexnow_key in config for a site.');
                continue;
            }

            $jsonPath = "indexnow/{$domain}.json";

            $this->line("🔍 Processing site: {$domain}");

            if (!$disk->exists($jsonPath)) {
                $this->error("❌ URLs JSON file not found: storage/app/public/{$jsonPath}");
                continue;
            }

            $jsonContent = $disk->get($jsonPath);
            $urls = json_decode($jsonContent, true);

            $cleanUrls = collect($urls)
                ->filter(fn($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL))
                ->values();

            if ($cleanUrls->isEmpty()) {
                $this->error("❌ No valid URLs found in JSON for {$domain}");
                continue;
            }

            $this->info("🚀 Submitting " . $cleanUrls->count() . " URLs individually to IndexNow engines...");

            foreach ($cleanUrls as $url) {
                foreach ($this->searchEngines as $engineName => $endpoint) {
                    $submitUrl = "{$endpoint}?url={$url}&key={$indexNowKey}";

                    try {
                        $response = Http::get($submitUrl);

                        if ($response->successful()) {
                            $this->line("✅ [{$engineName}] Accepted URL: {$url}");
                        } else {
                            $this->error("❌ [{$engineName}] Rejected URL: {$url} - HTTP {$response->status()}");
                        }
                    } catch (\Exception $e) {
                        $this->error("❌ [{$engineName}] Exception for URL {$url}: " . $e->getMessage());
                    }
                }
            }

            $this->line(str_repeat('-', 50));
        }

        $this->info("🎉 IndexNow submission process completed.");

        return 0;
    }
}
