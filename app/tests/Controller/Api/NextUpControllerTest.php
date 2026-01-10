<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\NextUpController;
use App\Document\Episode as EpisodeDocument;
use App\Helper\NextUpHelper;
use App\Repository\Episode as EpisodeRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
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
    private DocumentManager $documentManager;

    public function setUp(): void
    {
        $this->nextUpEpisodeHelper = Mockery::mock(NextUpHelper::class);
        $this->episodeRepository = Mockery::mock(EpisodeRepository::class);
        $this->documentManager = Mockery::mock(DocumentManager::class);
        $this->episodeDocument = new EpisodeDocument();
        $this->episodeDocument->seriesTitle = 'series title';
        $this->episodeDocument->season = 1;
        $this->episodeDocument->episode = 1;

        $container = Mockery::mock(ContainerInterface::class);
        $container->expects('has')->with('serializer')->andReturnFalse();

        $this->unit = new NextUpController();
        $this->unit->setContainer($container);
    }

    public function testSearchReturnsJsonResponseFromSeriesNotOnRecentlyWatchedList(): void
    {
        $this->documentManager->expects('getRepository')->andReturn($this->episodeRepository);
        $this->episodeRepository->expects('getAllUnwatchedEpisodes')->andReturn([]);
        $this->episodeRepository->expects('findBy')->andReturn([]);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }

    public function testSearchReturnsJsonResponseFromRecentlyWatchedList(): void
    {
        $this->documentManager->expects('getRepository')->andReturn($this->episodeRepository);
        $this->episodeRepository->expects('getAllUnwatchedEpisodes')->andReturn([]);
        $this->episodeRepository->expects('findBy')->andReturn([]);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }

    public function testSearchReturnsJsonResponseWhenItCantFindEpisodeFromRepository(): void
    {
        $this->documentManager->expects('getRepository')->andReturn($this->episodeRepository);
        $this->episodeRepository->expects('getAllUnwatchedEpisodes')->andReturn([]);
        $this->episodeRepository->expects('findBy')->andReturn([]);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }

    public function testSearchReturnsJsonResponseFromEmptyList(): void
    {
        $this->documentManager->expects('getRepository')->andReturn($this->episodeRepository);
        $this->episodeRepository->expects('getAllUnwatchedEpisodes')->andReturn([]);
        $this->episodeRepository->expects('findBy')->andReturn([]);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }
}
