<?php

namespace App\Tests\Entity\Api\Tvdb\Search;

use App\Entity\Api\Tvdb\Search\SeriesTitleFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SeriesTitleFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private SeriesTitleFactory $unit;
    private RequestStack&MockInterface $requestStack;

    public function setUp(): void
    {
        $this->requestStack = Mockery::mock(RequestStack::class);
        $this->unit = new SeriesTitleFactory();
    }

    public function testBuildFromRequestStackThrowsExceptionWhenRequestIsInvalid(): void
    {
        $this->requestStack->expects('getCurrentRequest')->andReturnNull();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('No request found');

        $this->unit->buildFromRequestStack($this->requestStack);
    }

    public function testBuildFromRequestStackThrowsExceptionWhenSeriesTitleIsNotProvided(): void
    {
        $request = Request::create('/test', 'GET', []);
        $this->requestStack->allows('getCurrentRequest')->andReturn($request);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('No series title provided');

        $this->unit->buildFromRequestStack($this->requestStack);
    }

    public function testBuildFromRequestStackReturnsSeriesTitleWhenSeriesTitleIsProvided(): void
    {
        $request = Request::create('/test', 'GET', ['seriesTitle' => 'series title']);
        $this->requestStack->allows('getCurrentRequest')->andReturn($request);

        $seriesTitle = $this->unit->buildFromRequestStack($this->requestStack);

        $this->assertSame('series title', $seriesTitle->title);
    }
}
