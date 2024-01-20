<?php

namespace App\Tests\Api;

use App\Api\TvdbQueryClient;
use App\Entity\Api\Tvdb\Search\SeriesTitle;
use App\Security\TvdbTokenProvider;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TvdbQueryClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TvdbQueryClient $unit;
    private HttpClientInterface $client;
    private TvdbTokenProvider $tokenProvider;

    public function setUp(): void
    {
        $this->client = Mockery::mock(HttpClientInterface::class);
        $this->tokenProvider = Mockery::mock(TvdbTokenProvider::class);

        $this->unit = new TvdbQueryClient($this->client, $this->tokenProvider);
    }

    public function testSearch(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->with(
                'GET',
                'https://api4.thetvdb.com/v4/search',
                [
                    'query' => [
                        'query' => 'query',
                        'type' => 'series'
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer token'
                    ]
                ]
            );

        $this->unit->search('query');
    }

    public function testSearchThrowsExceptionOnTransportException(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->andThrows($this->createMock(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while searching');
        $this->unit->search('query');
    }

    public function testSeriesExtended(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->with(
                'GET',
                'https://api4.thetvdb.com/v4/series/seriesId/extended',
                [
                    'headers' => [
                        'Authorization' => 'Bearer token'
                    ]
                ]
            );

        $this->unit->seriesExtended('seriesId');
    }

    public function testSeriesExtendedThrowsExceptionOnTransportException(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->andThrows($this->createMock(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while getting extended series data');
        $this->unit->seriesExtended('seriesId');
    }

    public function testSeasonExtended(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->with(
                'GET',
                'https://api4.thetvdb.com/v4/seasons/seasonId/extended',
                [
                    'headers' => [
                        'Authorization' => 'Bearer token'
                    ]
                ]
            );

        $this->unit->seasonExtended('seasonId');
    }

    public function testSeasonExtendedThrowsExceptionOnTransportException(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->andThrows($this->createMock(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while getting extended season data');
        $this->unit->seasonExtended('seasonId');
    }
}
