<?php

declare(strict_types=1);

namespace App\Processor;

use App\DataProvider\TvdbSeriesDataProvider;
use App\Document\Episode as EpisodeDocument;
use App\Entity\Ingest\Criteria;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use RuntimeException;

class Ingest
{
    public function __construct(
        private DocumentManager $documentManager,
        private TvdbSeriesDataProvider $tvdbSeriesDataProvider
    ) {
    }

    public function ingest(Criteria $criteria): void
    {
        $series = $this->tvdbSeriesDataProvider->getSeries(
            $criteria->tvdbSeriesId,
            $criteria->season,
            $criteria->episode
        );

        if ($series === null) {
            throw new RuntimeException('Series not found');
        }

        $episodeRepository = $this->documentManager->getRepository(EpisodeDocument::class);

        foreach ($series->getEpisodes() as $episode) {
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
            $episodeDocument->poster = $series->poster;
            $episodeDocument->universe = $criteria->universe;
            $episodeDocument->platform = $criteria->platform;
            $episodeDocument->status = EpisodeDocument::VALID_STATUSES[$series->status];
            $episodeDocument->airDate = new DateTimeImmutable($episode->aired);

            $this->documentManager->persist($episodeDocument);
            $this->documentManager->flush();
        }
    }
}
