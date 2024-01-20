<?php

namespace App\Tests\Cache;

use App\Api\TvdbAuthClient;
use App\Cache\TvdbApiTokenCache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TvdbApiTokenCacheTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TvdbApiTokenCache $unit;
    private CacheInterface $cache;
    private TvdbAuthClient $tvdbClient;


    public function setUp(): void
    {
        $this->cache = Mockery::mock(CacheInterface::class);
        $this->tvdbClient = Mockery::mock(TvdbAuthClient::class);

        $this->unit = new TvdbApiTokenCache($this->cache, $this->tvdbClient);
    }

    public function testGetToken(): void
    {
        $this->cache->expects('get')
            ->with(
                'tvdb_token',
                Mockery::on(function ($callback) {
                    $item = Mockery::mock(ItemInterface::class);
                    $item->expects('expiresAfter')
                        ->with(604800);
                    $callback($item);
                    return true;
                })
            )
            ->andReturns('token');

        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('toArray')
            ->andReturns(['data' => ['token' => 'token']]);

        $this->tvdbClient->expects('login')
            ->andReturns($response);

        $this->unit->getToken();
    }

    public function testGetTokenThrowsExceptionOnInvalidArgumentException(): void
    {
        $this->cache->expects('get')
            ->andThrows($this->createMock(InvalidArgumentException::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error getting token from cache');
        $this->unit->getToken();
    }
}
