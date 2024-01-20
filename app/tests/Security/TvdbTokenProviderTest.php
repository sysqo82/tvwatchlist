<?php

namespace App\Tests\Security;

use App\Cache\TvdbApiTokenCache;
use App\Security\TvdbTokenProvider;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class TvdbTokenProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetToken(): void
    {
        $tokenCache = Mockery::mock(TvdbApiTokenCache::class);
        $tokenCache->expects('getToken')->andReturn('token');

        $unit = new TvdbTokenProvider($tokenCache);
        $this->assertSame('token', $unit->getToken());
    }
}
