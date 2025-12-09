<?php

declare(strict_types=1);

namespace App\Processor;

use App\DataProvider\TvdbSeriesDataProvider;
use App\Document\Episode as EpisodeDocument;
use App\Document\Show as ShowDocument;
use App\Entity\Ingest\Criteria;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Ingest
{
    public function __construct(
        private DocumentManager $documentManager,
        private TvdbSeriesDataProvider $tvdbSeriesDataProvider,
        private LoggerInterface $logger
    ) {
    }

    public function ingest(Criteria $criteria): array
    {
        $this->logger->info("Starting ingestion for series ID: {$criteria->tvdbSeriesId}");
        
        $series = $this->tvdbSeriesDataProvider->getSeries(
            $criteria->tvdbSeriesId,
            $criteria->season,
            $criteria->episode
        );

        $this->logger->info("Series data retrieved: " . ($series ? $series->title : 'NULL'));

        if ($series === null) {
            $this->logger->error("Series not found for ID: {$criteria->tvdbSeriesId}");
            throw new RuntimeException('Series not found');
        }

        // Save or update the show record
        $showRepository = $this->documentManager->getRepository(ShowDocument::class);
        $showDocument = $showRepository->findOneBy(['tvdbSeriesId' => $criteria->tvdbSeriesId]);
        
        if (!$showDocument) {
            $showDocument = new ShowDocument();
            $showDocument->tvdbSeriesId = $criteria->tvdbSeriesId;
            $showDocument->addedAt = new DateTimeImmutable();
        }
        
        $showDocument->title = $series->title;
        $showDocument->poster = $series->getPoster();
        $showDocument->status = EpisodeDocument::VALID_STATUSES[$series->status] ?? 'upcoming';
        $showDocument->platform = $criteria->platform;
        $showDocument->universe = $criteria->universe;
        $showDocument->lastChecked = new DateTimeImmutable();
        
        $episodes = $series->getEpisodes();
        $episodeCount = count($episodes);
        $showDocument->hasEpisodes = $episodeCount > 0;
        
        $this->documentManager->persist($showDocument);
        $this->documentManager->flush();
        
        $this->logger->info("Ingesting series: {$series->title}, Found {$episodeCount} episodes");

        $episodeRepository = $this->documentManager->getRepository(EpisodeDocument::class);

        foreach ($episodes as $episode) {
            $episodeDocument = $episodeRepository->findOneBy([
                'tvdbEpisodeId' => $episode->tvdbId
            ]);

            if (!$episodeDocument) {
                $episodeDocument = new EpisodeDocument();
            }

            $episodeDocument->title = $episode->title;
            $episodeDocument->description = $episode->overview;
            $episodeDocument->season = $episode->seasonNumber;
            $episodeDocument->episode = $episode->number;
            $episodeDocument->tvdbEpisodeId = $episode->tvdbId;
            $episodeDocument->seriesTitle = $series->title;
            $episodeDocument->tvdbSeriesId = $series->tvdbId;
            $episodeDocument->poster = $series->getPoster();
            $episodeDocument->universe = $criteria->universe;
            $episodeDocument->platform = $criteria->platform;
            $episodeDocument->status = EpisodeDocument::VALID_STATUSES[$series->status];
            $episodeDocument->airDate = new DateTimeImmutable($episode->aired);

            $this->documentManager->persist($episodeDocument);
        }
        
        $this->documentManager->flush();
        
        return [
            'seriesTitle' => $series->title,
            'episodeCount' => $episodeCount
        ];
    }
}
