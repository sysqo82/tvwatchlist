<?php

namespace App\Tests\Entity\Ingest;

use App\Entity\Ingest\CriteriaFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CriteriaFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private CriteriaFactory $unit;
    private RequestStack&MockInterface $requestStack;
    private Request&MockInterface $request;

    public function setUp(): void
    {
        $this->request = Mockery::mock(Request::class);

        $this->requestStack = Mockery::mock(RequestStack::class);
        $this->requestStack->allows('getCurrentRequest')->andReturn($this->request)->byDefault();

        $this->unit = new CriteriaFactory();
    }

    public function testBuildFromRequestStackThrowsExceptionWhenRequestIsInvalid(): void
    {
        $this->requestStack->expects('getCurrentRequest')->andReturnNull();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('No request found');

        $this->unit->buildFromRequestStack($this->requestStack);
    }

    public function testBuildFromRequestStackThrowsExceptionWhenSeriesIdIsNotProvided(): void
    {
        $this->request->expects('getContent')->andReturn(json_encode([]));

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('seriesId is required');

        $this->unit->buildFromRequestStack($this->requestStack);
    }

    public function testBuildFromRequestStackReturnsDefaultCriteriaWhenOnlySeriesIdIsProvided(): void
    {
        $this->request->expects('getContent')->andReturn(json_encode(['seriesId' => 'series id']));

        $criteria = $this->unit->buildFromRequestStack($this->requestStack);

        $this->assertSame('series id', $criteria->tvdbSeriesId);
        $this->assertSame(1, $criteria->season);
        $this->assertSame(1, $criteria->episode);
        $this->assertSame('Plex', $criteria->platform);
    }

    public function testBuildFromRequestStackReturnsCriteriaWhenAllFieldsAreProvided(): void
    {
        $this->request->expects('getContent')->andReturn(json_encode([
            'seriesId' => 'series id',
            'season' => 2,
            'episode' => 3,
            'platform' => 'platform',
        ]));

        $criteria = $this->unit->buildFromRequestStack($this->requestStack);

        $this->assertSame('series id', $criteria->tvdbSeriesId);
        $this->assertSame(2, $criteria->season);
        $this->assertSame(3, $criteria->episode);
        $this->assertSame('platform', $criteria->platform);
    }
}
