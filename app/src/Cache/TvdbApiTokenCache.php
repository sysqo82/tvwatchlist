<?php

declare(strict_types=1);

namespace App\Cache;

use App\Api\TvdbAuthClient;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TvdbApiTokenCache
{
    public function __construct(
        private CacheInterface $cache,
        private TvdbAuthClient $tvdbClient
    ) {
    }

    public function getToken(): string
    {
        try {
            return $this->cache->get('tvdb_token', function (ItemInterface $item) {
                $item->expiresAfter(60 * 60 * 24 * 7); // 7 days

                $response = $this->tvdbClient->login();
                $data = $response->toArray();

                return $data['data']['token'];
            });
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException('Error getting token from cache', 0, $e);
        }
    }
}
