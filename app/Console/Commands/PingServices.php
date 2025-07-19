<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PingServices extends Command
{
    protected $signature   = 'ping:services';
    protected $description = 'Send XML-RPC pings to multiple services for all sites defined in config/indexnow.php';

    public function handle()
    {
        $sites = config('indexnow');

        if (empty($sites) || ! is_array($sites)) {
            $this->error('❌ No sites found in config/indexnow.php');
            return 1;
        }

        foreach ($sites as $site) {
            $domain   = $site['domain'] ?? null;
            $name     = $site['name'] ?? $domain;
            $feedUrl  = $site['feed_url'] ?? null;
            $services = $site['services'] ?? [];

            if (! $domain || empty($services)) {
                $this->warn("⚠️ Skipping: {$name} — Missing domain or services.");
                continue;
            }

            $blogName = $name;
            $blogUrl  = "https://{$domain}/";

            $this->newLine();
            $this->line("🔍 Processing site: <fg=cyan>{$blogName}</>");
            $this->line("🌐 Domain URL:     <fg=blue>{$blogUrl}</>");
            if ($feedUrl) {
                $this->line("📡 Feed URL:       <fg=yellow>{$feedUrl}</>");
            }
            $this->line(str_repeat('─', 60));

            foreach ($services as $serviceName => $endpoint) {
                $isExtended = Str::contains($serviceName, ['Twingly', 'Bitacoras']);

                $xml = $isExtended && $feedUrl
                ? $this->extendedPingXML($blogName, $blogUrl, $feedUrl)
                : $this->simplePingXML($blogName, $blogUrl);

                $this->line("\n📤 Sending ping to: <fg=magenta>{$serviceName}</>");
                $this->line("➡️ Endpoint:        <fg=gray>{$endpoint}</>");
                $this->line("📝 XML Payload:\n" . $this->highlightXml($xml));

                try {
                    $response = Http::timeout(10)
                        ->withHeaders([
                            'User-Agent'   => 'SEOIndexer/1.0',
                            'Content-Type' => 'text/xml; charset=UTF-8',
                            'Accept'       => 'text/xml',
                        ])
                        ->withBody($xml, 'text/xml; charset=UTF-8')
                        ->post($endpoint);

                    $status = $response->status();
                    $body   = trim($response->body());

                    $this->line("🔁 HTTP Status: <fg=cyan>{$status}</>");

                    if ($response->successful() && ! Str::contains($body, '<fault>')) {
                        $this->info("✅ Success from: {$serviceName}");
                    } else {
                        $this->warn("⚠️ Fault or error from: {$serviceName}");
                        $this->line("📨 Raw Response:\n" . $this->truncateXml($body));
                    }
                } catch (\Exception $e) {
                    $this->error("❌ Exception for {$serviceName}: " . $e->getMessage());
                }

                sleep(1); // Prevent flood
            }

            $this->line("\n✅ Completed pings for <fg=green>{$blogName}</>\n" . str_repeat('═', 60));
        }

        $this->newLine();
        $this->info("🎉 All sites processed.");
        return 0;
    }

    protected function truncateXml($xml, $maxLength = 300)
    {
        return strlen($xml) > $maxLength
        ? substr($xml, 0, $maxLength) . "... (truncated)"
        : $xml;
    }

    protected function highlightXml($xml)
    {
        return collect(explode("\n", $xml))
            ->map(fn($line) => "   <fg=gray>" . htmlentities($line) . "</>")
            ->implode("\n");
    }

    protected function simplePingXML($title, $url)
    {
        return <<<XML
<?xml version="1.0"?>
<methodCall>
   <methodName>weblogUpdates.ping</methodName>
   <params>
      <param><value><string>{$title}</string></value></param>
      <param><value><string>{$url}</string></value></param>
   </params>
</methodCall>
XML;
    }

    protected function extendedPingXML($title, $url, $feedUrl)
    {
        return <<<XML
<?xml version="1.0"?>
<methodCall>
   <methodName>weblogUpdates.extendedPing</methodName>
   <params>
      <param><value><string>{$title}</string></value></param>
      <param><value><string>{$url}</string></value></param>
      <param><value><string>{$feedUrl}</string></value></param>
   </params>
</methodCall>
XML;
    }
}
