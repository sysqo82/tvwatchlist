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
    /** @var HttpClientInterface|\Mockery\MockInterface */
    private $client;
    /** @var TvdbTokenProvider|\Mockery\MockInterface */
    private $tokenProvider;

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

    public function testSearchMovies(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->with(
                'GET',
                'https://api4.thetvdb.com/v4/search',
                [
                    'query' => [
                        'query' => 'movie title',
                        'type' => 'movie'
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer token'
                    ]
                ]
            );

        $this->unit->searchMovies('movie title');
    }

    public function testSearchMoviesThrowsExceptionOnTransportException(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->andThrows($this->createMock(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while searching for movies');
        $this->unit->searchMovies('movie title');
    }

    public function testMovieExtended(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->with(
                'GET',
                'https://api4.thetvdb.com/v4/movies/movieId/extended?meta=translations',
                [
                    'headers' => [
                        'Authorization' => 'Bearer token'
                    ]
                ]
            );

        $this->unit->movieExtended('movieId');
    }

    public function testMovieExtendedThrowsExceptionOnTransportException(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->andThrows($this->createMock(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while getting extended movie data');
        $this->unit->movieExtended('movieId');
    }

    public function testMovieTranslations(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->with(
                'GET',
                'https://api4.thetvdb.com/v4/movies/movieId/translations/eng',
                [
                    'headers' => [
                        'Authorization' => 'Bearer token'
                    ]
                ]
            );

        $this->unit->movieTranslations('movieId', 'eng');
    }

    public function testMovieTranslationsWithDefaultLanguage(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->with(
                'GET',
                'https://api4.thetvdb.com/v4/movies/movieId/translations/eng',
                [
                    'headers' => [
                        'Authorization' => 'Bearer token'
                    ]
                ]
            );

        $this->unit->movieTranslations('movieId');
    }

    public function testMovieTranslationsThrowsExceptionOnTransportException(): void
    {
        $this->tokenProvider->expects('getToken')
            ->andReturns('token');

        $this->client->expects('request')
            ->andThrows($this->createMock(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while getting movie translations');
        $this->unit->movieTranslations('movieId');
    }
}
