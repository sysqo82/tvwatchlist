<?php

namespace App\Tests\Repository;

use App\Document\Episode as EpisodeDocument;
use App\Repository\Episode;
use DG\BypassFinals;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class EpisodeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Episode $unit;
    private DocumentManager $documentManager;
    private Builder $queryBuilder;


    public function setUp(): void
    {
        BypassFinals::enable();

        $this->queryBuilder = Mockery::mock(Builder::class);

        $this->documentManager = Mockery::mock(DocumentManager::class);
        $this->documentManager->allows('createQueryBuilder')
            ->with(EpisodeDocument::class)
            ->andReturn($this->queryBuilder);

        $this->unit = new Episode($this->documentManager);
    }

    public function testGetLatestUnwatchedFromSeriesReturnEpisode()
    {
        $this->queryBuilder->expects('field')->with('seriesTitle')->andReturnSelf();
        $this->queryBuilder->expects('equals')->with('series')->andReturnSelf();
        $this->queryBuilder->expects('field')->with('watched')->andReturnSelf();
        $this->queryBuilder->expects('equals')->with(false)->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('season', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('episode', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('limit')->with(1)->andReturnSelf();

        $queryMock = Mockery::mock(Query::class);
        $this->queryBuilder->expects('getQuery')->andReturn($queryMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $queryMock->expects('execute')->andReturn($iteratorMock);

        $expected = new EpisodeDocument();
        $iteratorMock->expects('toArray')->andReturn([$expected]);

        $this->assertSame($expected, $this->unit->getLatestUnwatchedFromSeries('series'));
    }

    public function testGetLatestUnwatchFromSeriesReturnsNull()
    {
        $this->queryBuilder->expects('field')->with('seriesTitle')->andReturnSelf();
        $this->queryBuilder->expects('equals')->with('series')->andReturnSelf();
        $this->queryBuilder->expects('field')->with('watched')->andReturnSelf();
        $this->queryBuilder->expects('equals')->with(false)->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('season', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('episode', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('limit')->with(1)->andReturnSelf();

        $queryMock = Mockery::mock(Query::class);
        $this->queryBuilder->expects('getQuery')->andReturn($queryMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $queryMock->expects('execute')->andReturn($iteratorMock);

        $iteratorMock->expects('toArray')->andReturn([]);

        $this->assertNull($this->unit->getLatestUnwatchedFromSeries('series'));
    }

    public function testGetFirstEpisodeForSeriesReturnsEpisode()
    {
        $this->queryBuilder->expects('field')->with('seriesTitle')->andReturnSelf();
        $this->queryBuilder->expects('equals')->with('series')->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('season', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('episode', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('limit')->with(1)->andReturnSelf();

        $queryMock = Mockery::mock(Query::class);
        $this->queryBuilder->expects('getQuery')->andReturn($queryMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $queryMock->expects('execute')->andReturn($iteratorMock);

        $expected = new EpisodeDocument();
        $iteratorMock->expects('toArray')->andReturn([$expected]);

        $this->assertSame($expected, $this->unit->getFirstEpisodeForSeries('series'));
    }

    public function testGetFirstEpisodeForSeriesReturnsNull()
    {
        $this->queryBuilder->expects('field')->with('seriesTitle')->andReturnSelf();
        $this->queryBuilder->expects('equals')->with('series')->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('season', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('sort')->with('episode', 'ASC')->andReturnSelf();
        $this->queryBuilder->expects('limit')->with(1)->andReturnSelf();

        $queryMock = Mockery::mock(Query::class);
        $this->queryBuilder->expects('getQuery')->andReturn($queryMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $queryMock->expects('execute')->andReturn($iteratorMock);

        $iteratorMock->expects('toArray')->andReturn([]);

        $this->assertNull($this->unit->getFirstEpisodeForSeries('series'));
    }

    public function testDeleteEpisodesWithTvdbSeriesId()
    {
        $queryMock = Mockery::mock(Query::class);

        $this->queryBuilder->expects('remove')->andReturnSelf();
        $this->queryBuilder->expects('field')->with('tvdbSeriesId')->andReturnSelf();
        $this->queryBuilder->expects('equals')->with('12345')->andReturnSelf();
        $this->queryBuilder->expects('getQuery')->andReturn($queryMock);

        $queryMock->expects('execute');

        $this->unit->deleteEpisodesWithTvdbSeriesId('12345');
    }
}
