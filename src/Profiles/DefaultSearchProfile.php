<?php

namespace Spatie\SiteSearch\Profiles;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\SiteSearch\Indexers\DefaultIndexer;

class DefaultSearchProfile implements SearchProfile
{
    public function shouldCrawl(UriInterface $uriInterface): bool
    {
        return true;
    }

    public function shouldIndex(UriInterface $url): bool
    {
        ray("Should index {$url}")->blue();

        return true;
    }

    public function useIndexer(UriInterface $url, ResponseInterface $response): ?DefaultIndexer
    {
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return new DefaultIndexer($url, $response);
    }
}
