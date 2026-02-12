<?php

namespace Componist\DeveloperTools\Class;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;

class JsonCrawler
{
    protected Client $client;

    protected string $file;

    public ?string $baseDomain;

    protected string $baseScheme;

    public function __construct(?string $baseUrl = null, ?string $file = null)
    {
        //dd(request()->cookies->all());

        // dd(request()->cookies->all(), parse_url(env('APP_URL'),PHP_URL_HOST));

        $this->client = new Client([
            'timeout' => 60,
            'connect_timeout' => 30,
            'allow_redirects' => true,
            'verify' => false,
            //'cookies' =>  CookieJar::fromArray(request()->cookies->all(), parse_url(env('APP_URL'),PHP_URL_HOST)),
            // 'headers' => [
            //     'User-Agent' => 'ComponistCrawler/1.0 (+'.env('APP_URL').')'
            // ],
        ]);

        $this->file = $file ?? storage_path('app/crawler.json');

        $this->baseDomain = $baseUrl ? parse_url($baseUrl, PHP_URL_HOST) : null;
        $this->baseScheme = $baseUrl ? (parse_url($baseUrl, PHP_URL_SCHEME) ?? 'https') : 'https';

        if (! file_exists($this->file)) {
            $this->saveJson(['urls' => []]);
        }

        if ($baseUrl && empty($this->loadJson()['urls'])) {
            $this->saveJson([
                'urls' => [[
                    'url' => rtrim($baseUrl, '/'),
                    'visited' => false,
                    'status_code' => null,
                    'can_visit' => true,
                ]],
            ]);
        }
    }

    public function getData(): array
    {
        return $this->loadJson();
    }

    public function clearData(): void
    {
        $this->saveJson(['urls' => []]);
    }

    public function crawlNext(): bool
    {
        $data = $this->loadJson();

        // Nächste URL zum Crawlen finden (nur intern)
        $urlKey = null;
        foreach ($data['urls'] as $key => $entry) {
            if (! $entry['visited'] && $entry['can_visit']) {
                $urlKey = $key;
                break;
            }
        }

        if ($urlKey === null) {
            return false; // keine URLs mehr zum Crawlen
        }

        $entry = &$data['urls'][$urlKey];
        $urlToFetch = $entry['url'];

        try {
            if ($entry['can_visit']) {
                $response = $this->client->get($urlToFetch,[
                    'timeout' => 120
                ]);
                $status = $response->getStatusCode();
                $html = (string) $response->getBody();

                $entry['visited'] = true;
                $entry['status_code'] = $status;

                $crawler = new Crawler($html);
                $crawler->filter('a')->each(function (Crawler $node) use (&$data): void {
                    $href = $node->attr('href');
                    if (! $href) {
                        return;
                    }

                    $normalized = $this->normalizeUrl($href);
                    if ($normalized && ! $this->urlExists($normalized, $data)) {
                        $host = parse_url($normalized, PHP_URL_HOST);

                        $status_code = null;
                        $visited = false;
                        $can_visit = $host === $this->baseDomain;

                        if (str_contains($normalized, 'tel:')) {
                            $visited = false;
                            $status_code = 'tel';
                            $can_visit = false;
                        }

                        if (str_contains($normalized, 'mailto:')) {
                            $visited = false;
                            $status_code = 'mail';
                            $can_visit = false;
                        }

                        if (str_contains($normalized, '#')) {
                            $visited = false;
                            $status_code = '#';
                            $can_visit = false;
                        }

                        $data['urls'][] = [
                            'url' => $normalized,
                            'visited' => $visited,
                            'status_code' => $status_code,
                            'can_visit' => $can_visit,
                        ];
                    }
                });
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $entry['visited'] = true;
            $entry['status_code'] = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'timeout';

            // Optional: Loggen oder ausgeben
            error_log('Fehler beim Abrufen: ' . $entry['url'] . ' - ' . $e->getMessage());
        }

        $this->saveJson($data);

        return true;
    }

    private function urlExists(string $url, array $data): bool
    {
        foreach ($data['urls'] as $entry) {
            if ($entry['url'] === $url) {
                return true;
            }
        }

        return false;
    }

    private function normalizeUrl(string $url): ?string
    {
        $url = trim($url);

        // absolute URL
        if (preg_match('#^https?://#i', $url)) {
            return rtrim($url, '/');
        }

        if ($this->baseDomain === null) {
            return null;
        }

        $base = $this->baseScheme.'://'.$this->baseDomain;

        // root-relative
        if (str_starts_with($url, '/')) {
            return $base.rtrim($url, '/');
        }

        // relative Pfade wie "about" oder "folder/page"
        return $base.'/'.ltrim($url, '/');
    }

    private function loadJson(): array
    {
        $data = json_decode(file_get_contents($this->file), true);

        return $data ?: ['urls' => []];
    }

    private function saveJson(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }
}