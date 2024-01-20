<?php

namespace App\Tests\Processor;

use App\DataProvider\TvdbSeriesDataProvider;
use App\Document\Episode as EpisodeDocument;
use App\Entity\Ingest\Criteria;
use App\Entity\Tvdb\Episode;
use App\Entity\Tvdb\Series;
use App\Processor\Ingest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class IngestTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Ingest $unit;
    private DocumentManager $documentManager;
    private TvdbSeriesDataProvider $seriesDataProvider;

    public function setUp(): void
    {
        $this->documentManager = Mockery::mock(DocumentManager::class);

        $this->seriesDataProvider = Mockery::mock(TvdbSeriesDataProvider::class);

        $this->unit = new Ingest(
            $this->documentManager,
            $this->seriesDataProvider
        );
    }

    public function testIngestThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Series not found');

        $this->seriesDataProvider->expects('getSeries')
            ->with('tvdbId', 1, 1)
            ->andReturn(null);

        $this->unit->ingest(new Criteria('tvdbId', 1, 1, '', ''));
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testIngestPersistsNewEpisode(): void
    {
        $series = new Series(
            '123',
            'Test Series',
            'https://www.thetvdb.com/banners/posters/5b3e0b2d9d0c5.jpg',
            1
        );
        $series->addEpisode(new Episode(
            '1',
            'Test Episode',
            'Test Overview',
            '2021-01-01',
            1,
            1
        ));

        $this->seriesDataProvider->expects('getSeries')
            ->with('tvdbId', 1, 1)
            ->andReturn($series);

        $episodeRepository = Mockery::mock(DocumentRepository::class);
        $episodeRepository->expects('findOneBy')
            ->with(['tvdbEpisodeId' => '1'])
            ->andReturn(null);

        $this->documentManager->expects('getRepository')
            ->with(EpisodeDocument::class)
            ->andReturn($episodeRepository);

        $this->documentManager->expects('persist')
            ->with(Mockery::on(function ($episode) {
                return $episode instanceof EpisodeDocument
                    && $episode->tvdbEpisodeId === '1'
                    && $episode->title === 'Test Episode'
                    && $episode->description === 'Test Overview'
                    && $episode->season === 1
                    && $episode->episode === 1
                    && $episode->seriesTitle === 'Test Series'
                    && $episode->tvdbSeriesId === '123'
                    && $episode->poster === 'https://www.thetvdb.com/banners/posters/5b3e0b2d9d0c5.jpg'
                    && $episode->universe === ''
                    && $episode->platform === ''
                    && $episode->status === 'airing'
                    && $episode->airDate->format('Y-m-d') === '2021-01-01';
            }));

        $this->documentManager->expects('flush');

        $this->unit->ingest(new Criteria('tvdbId', 1, 1, '', ''));
    }

    public function testIngestUpdatesExistingEpisode(): void
    {
        $series = new Series(
            '123',
            'Test Series',
            'https://www.thetvdb.com/banners/posters/5b3e0b2d9d0c5.jpg',
            2
        );
        $series->addEpisode(new Episode(
            '1',
            'Test Episode',
            'Test Overview',
            '2021-01-01',
            1,
            1
        ));

        $this->seriesDataProvider->expects('getSeries')
            ->with('tvdbId', 1, 1)
            ->andReturn($series);

        $episodeRepository = Mockery::mock(DocumentRepository::class);
        $episodeDocument = new EpisodeDocument();
        $episodeDocument->status = 'airing';
        $episodeRepository->expects('findOneBy')
            ->with(['tvdbEpisodeId' => '1'])
            ->andReturn($episodeDocument);

        $this->documentManager->expects('getRepository')
            ->with(EpisodeDocument::class)
            ->andReturn($episodeRepository);

        $this->documentManager->expects('persist')
            ->with(Mockery::on(function ($episode) {
                return $episode instanceof EpisodeDocument
                    && $episode->status === 'finished'
                    && $episode->airDate->format('Y-m-d') === '2021-01-01';
            }));

        $this->documentManager->expects('flush');

        $this->unit->ingest(new Criteria('tvdbId', 1, 1, '', ''));
    }
}
