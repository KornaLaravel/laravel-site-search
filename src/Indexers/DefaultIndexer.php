<?php

namespace Spatie\SiteSearch\Indexers;

use Carbon\CarbonInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler;

class DefaultIndexer
{
    protected Crawler $domCrawler;

    public function __construct(
        protected UriInterface $url,
        protected ResponseInterface $response
    ) {
        $html = (string)$this->response->getBody();

        $this->domCrawler = new Crawler($html);
    }

    public function pageTitle(): ?string
    {
        return attempt(fn () => $this->domCrawler->filter('title')->first()->text());
    }

    public function h1(): ?string
    {
        return attempt(fn () => strip_tags($this->domCrawler->filter('h1')->first()->text()));
    }

    public function description(): ?string
    {
        $description = attempt(fn () => $this->domCrawler->filterXPath("//meta[@name='description']")->attr('content'));

        return preg_replace('/\s+/', ' ', $description);
    }

    public function entries(): array
    {
        $content = attempt(fn () => $this->domCrawler->filter('body')->first()->html());

        if (is_null($content)) {
            return [];
        }

        $content = strip_tags($content);

        $entries = array_map('trim', explode(PHP_EOL, $content));

        $entries = array_filter($entries);

        $entries = array_filter($entries, function (string $entry) {
            if (str_starts_with($entry, '{"')) {
                return false;
            }

            if (str_starts_with($entry, '/')) {
                return false;
            }

            if (str_starts_with($entry, '.')) {
                return false;
            }

            return strlen($entry) > 3;
        });

        return array_filter($entries);
    }

    public function dateModified(): ?CarbonInterface
    {
        return now();
    }
}