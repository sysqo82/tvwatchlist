<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\NextUpController;
use App\Document\Episode as EpisodeDocument;
use App\Helper\NextUpHelper;
use App\Repository\Episode as EpisodeRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NextUpControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private NextUpController $unit;
    private NextUpHelper $nextUpEpisodeHelper;
    private EpisodeRepository $episodeRepository;
    private EpisodeDocument $episodeDocument;

    public function setUp(): void
    {
        $this->nextUpEpisodeHelper = Mockery::mock(NextUpHelper::class);
        $this->episodeRepository = Mockery::mock(EpisodeRepository::class);
        $this->episodeDocument = new EpisodeDocument();
        $this->episodeDocument->seriesTitle = 'series title';
        $this->episodeDocument->season = 1;
        $this->episodeDocument->episode = 1;

        $container = Mockery::mock(ContainerInterface::class);
        $container->expects('has')->with('serializer')->andReturnFalse();

        $this->unit = new NextUpController($this->nextUpEpisodeHelper, $this->episodeRepository);
        $this->unit->setContainer($container);
    }

    public function testSearchReturnsJsonResponseFromSeriesNotOnRecentlyWatchedList(): void
    {
        $this->nextUpEpisodeHelper->expects('getSeriesNotOnRecentlyWatchedList')
            ->andReturn('series title');
        $this->episodeRepository->expects('getLatestUnwatchedFromSeries')
            ->with('series title')
            ->andReturn($this->episodeDocument);
        $response = $this->unit->search();
        $this->assertEquals(json_encode($this->episodeDocument), $response->getContent());
    }

    public function testSearchReturnsJsonResponseFromRecentlyWatchedList(): void
    {
        $this->nextUpEpisodeHelper->expects('getSeriesNotOnRecentlyWatchedList')->andReturn('');
        $this->nextUpEpisodeHelper->expects('getSeriesFromRecentlyWatchedList')->andReturn('series title');
        $this->episodeRepository->expects('getLatestUnwatchedFromSeries')
            ->with('series title')->andReturn($this->episodeDocument);
        $response = $this->unit->search();
        $this->assertEquals(json_encode($this->episodeDocument), $response->getContent());
    }

    public function testSearchReturnsJsonResponseWhenItCantFindEpisodeFromRepository(): void
    {
        $this->nextUpEpisodeHelper->expects('getSeriesNotOnRecentlyWatchedList')->andReturn('');
        $this->nextUpEpisodeHelper->expects('getSeriesFromRecentlyWatchedList')->andReturn('series title');
        $this->episodeRepository->expects('getLatestUnwatchedFromSeries')
            ->with('series title')->andReturn(null);
        $response = $this->unit->search();
        $this->assertEquals('[]', $response->getContent());
    }

    public function testSearchReturnsJsonResponseFromEmptyList(): void
    {
        $this->nextUpEpisodeHelper->expects('getSeriesNotOnRecentlyWatchedList')->andReturn('');
        $this->nextUpEpisodeHelper->expects('getSeriesFromRecentlyWatchedList')->andReturn('');
        $this->episodeRepository->shouldNotReceive('getLatestUnwatchedFromSeries');
        $response = $this->unit->search();
        $this->assertEquals('[]', $response->getContent());
    }
}
