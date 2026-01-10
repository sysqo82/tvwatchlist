<?php

namespace App\Tests\Repository;

use App\Document\ArchivedSeries as ArchivedSeriesDocument;
use App\Document\Episode as EpisodeDocument;
use App\Repository\ArchivedSeries;
use DG\BypassFinals;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Query\Query;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ArchivedSeriesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ArchivedSeries $unit;
    /** @var DocumentManager|\Mockery\MockInterface */
    private $documentManager;

    public function setUp(): void
    {
        BypassFinals::enable();
        
        $this->documentManager = Mockery::mock(DocumentManager::class);
        $this->unit = new ArchivedSeries($this->documentManager);
    }

    public function testGetAllArchivedSeries(): void
    {
        $archivedSeries1 = new ArchivedSeriesDocument();
        $archivedSeries1->seriesTitle = 'Series 1';
        $archivedSeries1->tvdbSeriesId = '12345';
        
        $archivedSeries2 = new ArchivedSeriesDocument();
        $archivedSeries2->seriesTitle = 'Series 2';
        $archivedSeries2->tvdbSeriesId = '67890';
        
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([$archivedSeries1, $archivedSeries2]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->expects('sort')->with('archivedAt', 'DESC')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(ArchivedSeriesDocument::class)
            ->andReturn($queryBuilder);
        
        $result = $this->unit->getAllArchivedSeries();
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Series 1', $result[0]->seriesTitle);
        $this->assertEquals('Series 2', $result[1]->seriesTitle);
    }

    public function testArchiveSeriesByTvdbId(): void
    {
        $episode = new EpisodeDocument();
        $episode->seriesTitle = 'Test Series';
        $episode->tvdbSeriesId = '12345';
        $episode->poster = 'poster.jpg';
        $episode->universe = 'MCU';
        $episode->platform = 'Disney+';
        
        // Mock episode query
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([$episode]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->expects('field')->with('tvdbSeriesId')->andReturnSelf();
        $queryBuilder->expects('equals')->with('12345')->andReturnSelf();
        $queryBuilder->expects('sort')->with('season', 'ASC')->andReturnSelf();
        $queryBuilder->expects('sort')->with('episode', 'ASC')->andReturnSelf();
        $queryBuilder->expects('limit')->with(1)->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        // Mock count queries
        $countQuery1 = Mockery::mock(Query::class);
        $countQuery1->expects('execute')->andReturn(10);
        
        $countQueryBuilder1 = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $countQueryBuilder1->expects('field')->with('tvdbSeriesId')->andReturnSelf();
        $countQueryBuilder1->expects('equals')->with('12345')->andReturnSelf();
        $countQueryBuilder1->expects('count')->andReturnSelf();
        $countQueryBuilder1->expects('getQuery')->andReturn($countQuery1);
        
        $countQuery2 = Mockery::mock(Query::class);
        $countQuery2->expects('execute')->andReturn(5);
        
        $countQueryBuilder2 = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $countQueryBuilder2->expects('field')->with('tvdbSeriesId')->andReturnSelf();
        $countQueryBuilder2->expects('equals')->with('12345')->andReturnSelf();
        $countQueryBuilder2->expects('field')->with('watched')->andReturnSelf();
        $countQueryBuilder2->expects('equals')->with(true)->andReturnSelf();
        $countQueryBuilder2->expects('count')->andReturnSelf();
        $countQueryBuilder2->expects('getQuery')->andReturn($countQuery2);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(EpisodeDocument::class)
            ->times(3)
            ->andReturn($queryBuilder, $countQueryBuilder1, $countQueryBuilder2);
        
        $this->documentManager->expects('persist')
            ->with(Mockery::type(ArchivedSeriesDocument::class));
        
        $this->documentManager->expects('flush');
        
        $this->unit->archiveSeriesByTvdbId('12345');
    }

    public function testArchiveSeriesByTvdbIdThrowsExceptionWhenNoEpisodesFound(): void
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->allows('sort')->andReturnSelf();
        $queryBuilder->allows('limit')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(EpisodeDocument::class)
            ->andReturn($queryBuilder);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No episodes found for series ID: 12345');
        
        $this->unit->archiveSeriesByTvdbId('12345');
    }

    public function testRestoreSeriesFromArchive(): void
    {
        $archivedSeries = new ArchivedSeriesDocument();
        $archivedSeries->tvdbSeriesId = '12345';
        
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([$archivedSeries]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->expects('field')->with('tvdbSeriesId')->andReturnSelf();
        $queryBuilder->expects('equals')->with('12345')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(ArchivedSeriesDocument::class)
            ->andReturn($queryBuilder);
        
        $this->documentManager->expects('remove')->with($archivedSeries);
        $this->documentManager->expects('flush');
        
        $result = $this->unit->restoreSeriesFromArchive('12345');
        
        $this->assertTrue($result);
    }

    public function testRestoreSeriesFromArchiveReturnsFalseWhenSeriesNotFound(): void
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(ArchivedSeriesDocument::class)
            ->andReturn($queryBuilder);
        
        $result = $this->unit->restoreSeriesFromArchive('12345');
        
        $this->assertFalse($result);
    }

    public function testGetArchivedSeriesByTvdbId(): void
    {
        $archivedSeries = new ArchivedSeriesDocument();
        $archivedSeries->tvdbSeriesId = '12345';
        $archivedSeries->seriesTitle = 'Test Series';
        $archivedSeries->platform = 'Netflix';
        $archivedSeries->universe = 'MCU';
        $archivedSeries->poster = 'poster.jpg';
        $archivedSeries->overview = 'Test overview';
        $archivedSeries->network = 'Network';
        $archivedSeries->totalEpisodes = 10;
        $archivedSeries->watchedEpisodes = 5;
        $archivedSeries->archivedAt = new \DateTimeImmutable();
        $archivedSeries->archiveReason = 'Test reason';
        
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([$archivedSeries]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->expects('field')->with('tvdbSeriesId')->andReturnSelf();
        $queryBuilder->expects('equals')->with('12345')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(ArchivedSeriesDocument::class)
            ->andReturn($queryBuilder);
        
        $result = $this->unit->getArchivedSeriesByTvdbId('12345');
        
        $this->assertIsArray($result);
        $this->assertEquals('12345', $result['tvdbSeriesId']);
        $this->assertEquals('Test Series', $result['seriesTitle']);
        $this->assertEquals('Netflix', $result['platform']);
        $this->assertEquals('MCU', $result['universe']);
        $this->assertEquals(10, $result['totalEpisodes']);
        $this->assertEquals(5, $result['watchedEpisodes']);
    }

    public function testGetArchivedSeriesByTvdbIdReturnsNullWhenNotFound(): void
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(ArchivedSeriesDocument::class)
            ->andReturn($queryBuilder);
        
        $result = $this->unit->getArchivedSeriesByTvdbId('12345');
        
        $this->assertNull($result);
    }

    public function testPermanentlyDeleteFromArchive(): void
    {
        $archivedSeries = new ArchivedSeriesDocument();
        $archivedSeries->tvdbSeriesId = '12345';
        
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([$archivedSeries]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->expects('field')->with('tvdbSeriesId')->andReturnSelf();
        $queryBuilder->expects('equals')->with('12345')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(ArchivedSeriesDocument::class)
            ->andReturn($queryBuilder);
        
        $this->documentManager->expects('remove')->with($archivedSeries);
        $this->documentManager->expects('flush');
        
        $result = $this->unit->permanentlyDeleteFromArchive('12345');
        
        $this->assertTrue($result);
    }

    public function testPermanentlyDeleteFromArchiveReturnsFalseWhenSeriesNotFound(): void
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->expects('toArray')->andReturn([]);
        
        $query = Mockery::mock(Query::class);
        $query->expects('execute')->andReturn($iterator);
        
        $queryBuilder = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $queryBuilder->allows('field')->andReturnSelf();
        $queryBuilder->allows('equals')->andReturnSelf();
        $queryBuilder->expects('getQuery')->andReturn($query);
        
        $this->documentManager->expects('createQueryBuilder')
            ->with(ArchivedSeriesDocument::class)
            ->andReturn($queryBuilder);
        
        $result = $this->unit->permanentlyDeleteFromArchive('12345');
        
        $this->assertFalse($result);
    }
}
