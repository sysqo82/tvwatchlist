<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\NextUpController;
use App\Document\Episode as EpisodeDocument;
use App\Helper\NextUpHelper;
use App\Repository\Episode as EpisodeRepository;
use DG\BypassFinals;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Query\Query;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NextUpControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private NextUpController $unit;
    /** @var NextUpHelper|\Mockery\MockInterface */
    private $nextUpEpisodeHelper;
    /** @var EpisodeRepository|\Mockery\MockInterface */
    private $episodeRepository;
    private EpisodeDocument $episodeDocument;
    /** @var DocumentManager|\Mockery\MockInterface */
    private $documentManager;

    public function setUp(): void
    {
        BypassFinals::enable();
        
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
        $iterator = Mockery::mock(Iterator::class);
        $iterator->allows('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->allows('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->expects('field')->with('watched')->andReturnSelf();
        $queryBuilder->expects('equals')->with(false)->andReturnSelf();
        $queryBuilder->allows('sort')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')->andReturn($queryBuilder);
        
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $showRepo */
        $showRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $showRepo->expects('findBy')->with(['hasEpisodes' => false])->andReturn([]);
        
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $movieRepo */
        $movieRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $movieRepo->expects('findBy')->with(['watched' => false])->andReturn([]);
        
        $this->documentManager->expects('getRepository')->times(2)->andReturn($showRepo, $movieRepo);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }

    public function testSearchReturnsJsonResponseFromRecentlyWatchedList(): void
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->allows('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->allows('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->allows('sort')->andReturnSelf();
        $queryBuilder->allows('getQuery')->andReturn($query);
        $this->documentManager->expects('createQueryBuilder')->andReturn($queryBuilder);
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $showRepo */
        $showRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $showRepo->expects('findBy')->andReturn([]);
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $movieRepo */
        $movieRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $movieRepo->expects('findBy')->andReturn([]);
        $this->documentManager->expects('getRepository')->times(2)->andReturn($showRepo, $movieRepo);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }

    public function testSearchReturnsJsonResponseWhenItCantFindEpisodeFromRepository(): void
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->allows('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->allows('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->allows('sort')->andReturnSelf();
        $queryBuilder->allows('getQuery')->andReturn($query);
        $this->documentManager->expects('createQueryBuilder')->andReturn($queryBuilder);
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $showRepo */
        $showRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $showRepo->expects('findBy')->andReturn([]);
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $movieRepo */
        $movieRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $movieRepo->expects('findBy')->andReturn([]);
        $this->documentManager->expects('getRepository')->times(2)->andReturn($showRepo, $movieRepo);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }

    public function testSearchReturnsJsonResponseFromEmptyList(): void
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->allows('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->allows('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->allows('sort')->andReturnSelf();
        $queryBuilder->allows('getQuery')->andReturn($query);
        $this->documentManager->expects('createQueryBuilder')->andReturn($queryBuilder);
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $showRepo */
        $showRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $showRepo->expects('findBy')->andReturn([]);
        /** @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository|\Mockery\MockInterface $movieRepo */
        $movieRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $movieRepo->expects('findBy')->andReturn([]);
        $this->documentManager->expects('getRepository')->times(2)->andReturn($showRepo, $movieRepo);
        
        $response = $this->unit->search($this->documentManager);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
    }
}
