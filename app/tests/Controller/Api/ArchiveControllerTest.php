<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Controller\Api\ArchiveController;
use App\Document\ArchivedSeries as ArchivedSeriesDocument;
use App\Document\Episode as EpisodeDocument;
use App\Processor\Ingest;
use DG\BypassFinals;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Query\Query;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ArchiveControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ArchiveController $unit;
    /** @var DocumentManager&MockInterface */
    private DocumentManager $documentManager;
    /** @var Ingest&MockInterface */
    private Ingest $ingestProcessor;

    public function setUp(): void
    {
        BypassFinals::enable();

        $this->documentManager = Mockery::mock(DocumentManager::class);
        $this->ingestProcessor = Mockery::mock(Ingest::class);

        $container = Mockery::mock(ContainerInterface::class);
        $container->allows('has')->with('serializer')->andReturnFalse();

        $this->unit = new ArchiveController();
        $this->unit->setContainer($container);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Builds a query-builder mock whose execute()->toArray() chain returns $results.
     *
     * @return MockInterface
     */
    private function makeQueryBuilder(array $results): MockInterface
    {
        $iterator = Mockery::mock(Iterator::class);
        $iterator->allows('toArray')->andReturn($results);

        $query = Mockery::mock(Query::class);
        $query->allows('execute')->andReturn($iterator);

        $qb = Mockery::mock('\Doctrine\ODM\MongoDB\Query\Builder');
        $qb->allows('field')->andReturnSelf();
        $qb->allows('equals')->andReturnSelf();
        $qb->allows('sort')->andReturnSelf();
        $qb->allows('updateOne')->andReturnSelf();
        $qb->allows('set')->andReturnSelf();
        $qb->allows('remove')->andReturnSelf();
        $qb->allows('getQuery')->andReturn($query);

        return $qb;
    }

    /**
     * Creates a partially-mocked ArchivedSeriesDocument.
     *
     * @param array<array{season: int, episode: int}> $watchedEpisodesList
     */
    private function makeArchivedSeriesDocument(
        string $tvdbSeriesId = 'tvdb123',
        array $watchedEpisodesList = []
    ): ArchivedSeriesDocument {
        /** @var ArchivedSeriesDocument&MockInterface $doc */
        $doc = Mockery::mock(ArchivedSeriesDocument::class)->makePartial();
        $doc->seriesTitle = 'Test Series';
        $doc->tvdbSeriesId = $tvdbSeriesId;
        $doc->poster = 'poster.jpg';
        $doc->platform = 'Plex';
        $doc->overview = null;
        $doc->network = null;
        $doc->totalEpisodes = 3;
        $doc->watchedEpisodes = count($watchedEpisodesList);
        $doc->watchedEpisodesList = $watchedEpisodesList;
        $doc->archivedAt = new \DateTime();
        $doc->archiveReason = 'User removed';

        return $doc;
    }

    /** Creates an Episode document for a given series/season/episode. */
    private function makeEpisode(string $tvdbSeriesId, int $season, int $episode, bool $watched = false): EpisodeDocument
    {
        $ep = new EpisodeDocument();
        $ep->seriesTitle = 'Test Series';
        $ep->tvdbSeriesId = $tvdbSeriesId;
        $ep->title = 'Episode Title';
        $ep->description = 'Episode description';
        $ep->season = $season;
        $ep->episode = $episode;
        $ep->tvdbEpisodeId = 'tvdb_ep_' . $episode;
        $ep->poster = 'poster.jpg';
        $ep->platform = 'Plex';
        $ep->status = 'airing';
        $ep->watched = $watched;

        return $ep;
    }
    /**
     * Returns a mock MongoDB collection that accepts updateOne() calls.
     */
    private function makeMongoCollection(): MockInterface
    {
        $updateResult = Mockery::mock('\MongoDB\UpdateResult');
        $collection = Mockery::mock('\MongoDB\Collection');
        $collection->allows('updateOne')->andReturn($updateResult);
        return $collection;
    }

    /**
     * Wires documentManager to return a mock collection via getDocumentDatabase().
     */
    private function mockCollectionOnDocumentManager(MockInterface $dm, MockInterface $collection): void
    {
        $database = Mockery::mock('\MongoDB\Database');
        $database->allows('selectCollection')->andReturn($collection);
        $dm->allows('getDocumentDatabase')->andReturn($database);
    }


    public function testGetArchivedSeriesReturnsJsonResponse(): void
    {
        $this->documentManager->expects('createQueryBuilder')
            ->twice()
            ->andReturn(
                $this->makeQueryBuilder([]),
                $this->makeQueryBuilder([])
            );

        $response = $this->unit->getArchivedSeries($this->documentManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $body = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('archivedSeries', $body);
        $this->assertArrayHasKey('archivedMovies', $body);
    }

    // -------------------------------------------------------------------------
    // restoreSeries
    // -------------------------------------------------------------------------

    public function testRestoreSeriesReturnsNotFoundWhenSeriesNotInArchive(): void
    {
        $this->documentManager->expects('createQueryBuilder')
            ->once()
            ->andReturn($this->makeQueryBuilder([]));

        $response = $this->unit->restoreSeries('tvdb123', $this->documentManager, $this->ingestProcessor);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * Core bug-fix test: episodes stored in watchedEpisodesList at archive time
     * must be marked as watched on the freshly re-imported Episode documents when
     * the series is restored, regardless of what is (or isn't) in History.
     * This prevents the user marking them again and creating orphaned History entries.
     */
    public function testRestoreSeriesMarksWatchedEpisodesAsWatched(): void
    {
        $watchedList = [['season' => 1, 'episode' => 1]];
        $archivedDoc = $this->makeArchivedSeriesDocument('tvdb123', $watchedList);
        $episode     = $this->makeEpisode('tvdb123', 1, 1, false);

        $this->documentManager->expects('createQueryBuilder')
            ->twice()
            ->andReturn(
                $this->makeQueryBuilder([$archivedDoc]),
                $this->makeQueryBuilder([$archivedDoc])
            );

        $this->ingestProcessor->expects('ingest')->once();
        $this->documentManager->expects('clear')->once();

        $episodeRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $episodeRepo->expects('findOneBy')
            ->with(['tvdbSeriesId' => 'tvdb123', 'season' => 1, 'episode' => 1])
            ->andReturn($episode);

        $this->documentManager->expects('getRepository')->once()->andReturn($episodeRepo);
        $this->documentManager->expects('persist')->once()->with($episode);
        $this->documentManager->expects('flush')->twice();
        $this->documentManager->allows('remove');

        $response = $this->unit->restoreSeries('tvdb123', $this->documentManager, $this->ingestProcessor);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($episode->watched);
    }

    /**
     * Multiple watched episodes: all must be marked watched, each gets its own
     * findOneBy call.
     */
    public function testRestoreSeriesMarksAllWatchedEpisodes(): void
    {
        $watchedList = [
            ['season' => 1, 'episode' => 1],
            ['season' => 1, 'episode' => 2],
        ];
        $archivedDoc = $this->makeArchivedSeriesDocument('tvdb123', $watchedList);
        $ep1 = $this->makeEpisode('tvdb123', 1, 1, false);
        $ep2 = $this->makeEpisode('tvdb123', 1, 2, false);

        $this->documentManager->expects('createQueryBuilder')
            ->twice()
            ->andReturn(
                $this->makeQueryBuilder([$archivedDoc]),
                $this->makeQueryBuilder([$archivedDoc])
            );

        $this->ingestProcessor->expects('ingest')->once();
        $this->documentManager->expects('clear')->once();

        $episodeRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $episodeRepo->expects('findOneBy')
            ->with(['tvdbSeriesId' => 'tvdb123', 'season' => 1, 'episode' => 1])
            ->andReturn($ep1);
        $episodeRepo->expects('findOneBy')
            ->with(['tvdbSeriesId' => 'tvdb123', 'season' => 1, 'episode' => 2])
            ->andReturn($ep2);

        $this->documentManager->expects('getRepository')->twice()->andReturn($episodeRepo, $episodeRepo);
        $this->documentManager->expects('persist')->twice();
        $this->documentManager->expects('flush')->twice();
        $this->documentManager->allows('remove');

        $response = $this->unit->restoreSeries('tvdb123', $this->documentManager, $this->ingestProcessor);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($ep1->watched);
        $this->assertTrue($ep2->watched);
    }

    /**
     * Idempotency guard: if an episode is already watched (shouldn't normally
     * happen) persist must not be called for it.
     */
    public function testRestoreSeriesIdempotentForAlreadyWatchedEpisodes(): void
    {
        $watchedList = [['season' => 1, 'episode' => 2]];
        $archivedDoc = $this->makeArchivedSeriesDocument('tvdb123', $watchedList);
        $episode     = $this->makeEpisode('tvdb123', 1, 2, true);

        $this->documentManager->expects('createQueryBuilder')
            ->twice()
            ->andReturn(
                $this->makeQueryBuilder([$archivedDoc]),
                $this->makeQueryBuilder([$archivedDoc])
            );

        $this->ingestProcessor->expects('ingest')->once();
        $this->documentManager->expects('clear')->once();

        $episodeRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $episodeRepo->expects('findOneBy')->andReturn($episode);

        $this->documentManager->expects('getRepository')->once()->andReturn($episodeRepo);
        $this->documentManager->expects('persist')->once();
        $this->documentManager->expects('flush')->twice();
        $this->documentManager->allows('remove');

        $response = $this->unit->restoreSeries('tvdb123', $this->documentManager, $this->ingestProcessor);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Series with no watched episodes at archive time: no Episode lookups and no
     * flush needed (skipped for efficiency).
     */
    public function testRestoreSeriesSucceedsWithNoWatchedEpisodes(): void
    {
        $archivedDoc = $this->makeArchivedSeriesDocument('tvdb123', []);

        $this->documentManager->expects('createQueryBuilder')
            ->twice()
            ->andReturn(
                $this->makeQueryBuilder([$archivedDoc]),
                $this->makeQueryBuilder([$archivedDoc])
            );

        $this->ingestProcessor->expects('ingest')->once();
        $this->documentManager->expects('clear')->once();
        $this->documentManager->expects('getRepository')->never();
        $this->documentManager->expects('persist')->never();
        $this->documentManager->expects('flush')->once();
        $this->documentManager->allows('remove');

        $response = $this->unit->restoreSeries('tvdb123', $this->documentManager, $this->ingestProcessor);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Episode stored in watchedEpisodesList but no longer present after
     * re-import (removed from TVDB): findOneBy returns null, no persist attempted.
     * The History entry for it remains fully removable via the delete endpoint.
     */
    public function testRestoreSeriesHandlesEpisodeNoLongerPresentAfterIngest(): void
    {
        $watchedList = [['season' => 2, 'episode' => 1]];
        $archivedDoc = $this->makeArchivedSeriesDocument('tvdb123', $watchedList);

        $this->documentManager->expects('createQueryBuilder')
            ->twice()
            ->andReturn(
                $this->makeQueryBuilder([$archivedDoc]),
                $this->makeQueryBuilder([$archivedDoc])
            );

        $this->ingestProcessor->expects('ingest')->once();
        $this->documentManager->expects('clear')->once();

        $episodeRepo = Mockery::mock('\Doctrine\ODM\MongoDB\Repository\DocumentRepository');
        $episodeRepo->expects('findOneBy')->andReturn(null);

        $this->documentManager->expects('getRepository')->once()->andReturn($episodeRepo);
        $this->documentManager->expects('persist')->never();
        $this->documentManager->expects('flush')->twice();
        $this->documentManager->allows('remove');

        $response = $this->unit->restoreSeries('tvdb123', $this->documentManager, $this->ingestProcessor);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // permanentlyDeleteSeries
    // -------------------------------------------------------------------------

    public function testPermanentlyDeleteSeriesReturnsOkWhenSeriesExists(): void
    {
        $archivedDoc = $this->makeArchivedSeriesDocument('tvdb789');

        $this->documentManager->expects('createQueryBuilder')
            ->twice()
            ->andReturn(
                $this->makeQueryBuilder([$archivedDoc]), // ArchivedSeries lookup
                $this->makeQueryBuilder([])              // History bulk delete
            );

        $this->documentManager->expects('remove')->once()->with($archivedDoc);
        $this->documentManager->expects('flush')->once();

        $response = $this->unit->permanentlyDeleteSeries('tvdb789', $this->documentManager);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testPermanentlyDeleteSeriesReturnsNotFoundWhenMissing(): void
    {
        $this->documentManager->expects('createQueryBuilder')
            ->once()
            ->andReturn($this->makeQueryBuilder([]));

        $response = $this->unit->permanentlyDeleteSeries('tvdb_missing', $this->documentManager);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
