<?php

declare(strict_types=1);

namespace App\Api;

use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TvdbAuthClient extends TvdbClientBase
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apikey,
        private string $pin
    ) {
    }

    public function login(): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                'POST',
                self::TVDB_API_BASE_URL . 'login',
                [
                    'json' => [
                        'apikey' => $this->apikey,
                        'pin' => $this->pin
                    ]
                ]
            );
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Error while logging in', 0, $e);
        }
    }
}
