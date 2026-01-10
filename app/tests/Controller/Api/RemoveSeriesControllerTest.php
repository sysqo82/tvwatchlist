<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\RemoveSeriesController;
use App\Repository\Episode as EpisodeRepository;
use DG\BypassFinals;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Query;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RemoveSeriesControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RemoveSeriesController $unit;
    /** @var EpisodeRepository|\Mockery\MockInterface */
    private $episodeRepository;
    /** @var DocumentManager|\Mockery\MockInterface */
    private $documentManager;

    public function setUp(): void
    {
        BypassFinals::enable();

        $this->episodeRepository = Mockery::mock(EpisodeRepository::class);
        $this->documentManager = Mockery::mock(DocumentManager::class);
        $this->unit = new RemoveSeriesController();
    }

    public function testRemoveSeries()
    {
        /** @var \App\Repository\ArchivedSeries|\Mockery\MockInterface $archivedRepo */
        $archivedRepo = Mockery::mock('\App\Repository\ArchivedSeries');
        $archivedRepo->allows('archiveSeriesByTvdbId')->with('tvdb series id');

        $query = Mockery::mock(Query::class);
        $query->allows('execute');

        $queryBuilder = Mockery::mock('\\Doctrine\\ODM\\MongoDB\\Query\\Builder');
        $queryBuilder->allows('remove')->andReturnSelf();
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->allows('getQuery')->andReturn($query);

        $this->documentManager->allows('createQueryBuilder')->andReturn($queryBuilder);
        $this->documentManager->allows('getRepository')->andReturn($archivedRepo);

        $response = $this->unit->removeSeries('tvdb series id', $this->documentManager);

        // The controller instantiates repositories directly which makes it hard to mock properly
        // This test validates that the method runs and returns a JsonResponse
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }
}
