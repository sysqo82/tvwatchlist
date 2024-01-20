<?php

namespace App\Tests\Entity\Api\Tvdb\Search;

use App\Entity\Api\Tvdb\Search\SeriesTitleFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SeriesTitleFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private SeriesTitleFactory $unit;
    private RequestStack $requestStack;
    private Request $request;

    public function setUp(): void
    {
        $this->request = Mockery::mock(Request::class);
        $this->requestStack = Mockery::mock(RequestStack::class);
        $this->requestStack->allows('getCurrentRequest')->andReturn($this->request)->byDefault();

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
        $this->request->expects('get')->with('seriesTitle')->andReturnNull();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('No series title provided');

        $this->unit->buildFromRequestStack($this->requestStack);
    }

    public function testBuildFromRequestStackReturnsSeriesTitleWhenSeriesTitleIsProvided(): void
    {
        $this->request->expects('get')->with('seriesTitle')->andReturn('series title');

        $seriesTitle = $this->unit->buildFromRequestStack($this->requestStack);

        $this->assertSame('series title', $seriesTitle->title);
    }
}
