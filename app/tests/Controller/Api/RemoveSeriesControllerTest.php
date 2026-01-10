<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\RemoveSeriesController;
use App\Repository\Episode as EpisodeRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RemoveSeriesControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RemoveSeriesController $unit;
    private EpisodeRepository $episodeRepository;
    private DocumentManager $documentManager;

    public function setUp(): void
    {
        $this->episodeRepository = Mockery::mock(EpisodeRepository::class);
        $this->documentManager = Mockery::mock(DocumentManager::class);
        $this->unit = new RemoveSeriesController();
    }

    public function testRemoveSeries()
    {
        $archivedRepo = Mockery::mock();
        $archivedRepo->expects('archiveSeriesByTvdbId')->with('tvdb series id');
        $this->episodeRepository->expects('deleteEpisodesWithTvdbSeriesId')->with('tvdb series id');
        
        $this->documentManager->expects('getRepository')->andReturn($archivedRepo, $this->episodeRepository);
        
        $response = $this->unit->removeSeries('tvdb series id', $this->documentManager);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
