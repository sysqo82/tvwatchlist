<?php

declare(strict_types=1);

namespace App\Api;

use App\Security\TvdbTokenProvider;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TvdbQueryClient extends TvdbClientBase
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private TvdbTokenProvider $tokenProvider
    ) {
    }

    public function search(string $seriesTitle): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                'GET',
                self::TVDB_API_BASE_URL . 'search',
                [
                    'query' => [
                        'query' => $seriesTitle,
                        'type' => 'series'
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->tokenProvider->getToken()
                    ]
                ]
            );
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Error while searching', 0, $e);
        }
    }

    public function seriesExtended(string $seriesId): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                'GET',
                self::TVDB_API_BASE_URL . 'series/' . $seriesId . '/extended',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->tokenProvider->getToken()
                    ]
                ]
            );
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Error while getting extended series data', 0, $e);
        }
    }

    public function seasonExtended(string $seasonId): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                'GET',
                self::TVDB_API_BASE_URL . 'seasons/' . $seasonId . '/extended',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->tokenProvider->getToken()
                    ]
                ]
            );
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Error while getting extended season data', 0, $e);
        }
    }
}
