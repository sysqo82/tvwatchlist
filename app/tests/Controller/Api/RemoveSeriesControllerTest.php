<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\RemoveSeriesController;
use App\Repository\Episode as EpisodeRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RemoveSeriesControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RemoveSeriesController $unit;
    private EpisodeRepository $episodeRepository;

    public function setUp(): void
    {
        $this->episodeRepository = Mockery::mock(EpisodeRepository::class);
        $this->unit = new RemoveSeriesController($this->episodeRepository);
    }

    public function testRemoveSeries()
    {
        $this->episodeRepository->expects('deleteEpisodesWithTvdbSeriesId')->with('tvdb series id');
        $response = $this->unit->removeSeries('tvdb series id');
        $this->assertEquals(204, $response->getStatusCode());
    }
}
