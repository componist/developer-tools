<?php

namespace Componist\DeveloperTools\Livewire\Crawler;

use Componist\DeveloperTools\Class\JsonCrawler;
use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Livewire\Component;

class RoutesJson extends Component
{
    use AuthorizesDeveloperTools;

    public $baseUrl;

    public $running = false;

    public function startCrawler()
    {
        $this->running = true;
        new JsonCrawler($this->baseUrl); // nur initialisieren

        // Kickoff Loop im Frontend
        $this->js('window.dispatchEvent(new Event("crawler-loop"))');
    }

    public function crawlStep()
    {
        $crawler = new JsonCrawler; // ohne baseUrl, sonst überschreibt er!
        $crawler->baseDomain = parse_url($this->baseUrl, PHP_URL_HOST);

        $hasNext = $crawler->crawlNext();

        if (! $hasNext) {
            $this->running = false; // stop
        }
    }

    public function resetCrawler()
    {
        $this->running = false;
    }

    public function render()
    {
        $crawler = new JsonCrawler;
        $data = $crawler->getData();

        $content = [
            'urls' => array_reverse($data['urls']),
            'total' => count($data['urls']),
            'done' => count(array_filter($data['urls'], fn ($u) => $u['visited'])),
            'running' => $this->running,
        ];

        return view('developerTools::livewire.crawler.routes-json', compact('content'))->layout('developerTools::layouts.package');
    }

    public function clearJonsFile()
    {
        $crawler = new JsonCrawler;
        $crawler->clearData();
    }

    public function generateSitemap()
    {
        $crawler = new \Componist\DeveloperTools\Class\JsonCrawler;
        $data = $crawler->getData();

        // Nur URLs mit Status 200
        $urls = array_filter($data['urls'], fn ($url) => $url['status_code'] === 200);

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
<urlset />');
$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

foreach ($urls as $url) {
$urlNode = $xml->addChild('url');
$urlNode->addChild('loc', htmlspecialchars($url['url']));
$urlNode->addChild('lastmod', date('Y-m-d'));
$urlNode->addChild('changefreq', 'weekly');
$urlNode->addChild('priority', '0.8');
}

$path = public_path('sitemap.xml');
$xml->asXML($path);

}
}