<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\Episode as EpisodeDocument;

class Episode
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {
    }

    public function getLatestUnwatchedFromSeries(string $series): ?EpisodeDocument
    {
        $builder = $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->field('seriesTitle')->equals($series)
            ->field('watched')->equals(false)
            ->sort('season', 'ASC')
            ->sort('episode', 'ASC')
            ->limit(1);

        return $builder->getQuery()->execute()->toArray()[0] ?? null;
    }

    public function getFirstEpisodeForSeries(string $seriesTitle): ?EpisodeDocument
    {
        $builder = $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->field('seriesTitle')->equals($seriesTitle)
            ->sort('season', 'ASC')
            ->sort('episode', 'ASC')
            ->limit(1);

        return $builder->getQuery()->execute()->toArray()[0] ?? null;
    }

    public function deleteEpisodesWithTvdbSeriesId(string $tvdbSeriesId): void
    {
        $builder = $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->remove()
            ->field('tvdbSeriesId')->equals($tvdbSeriesId);

        $builder->getQuery()->execute();
    }

    public function getAllUnwatchedEpisodes(): array
    {
        $builder = $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->field('watched')->equals(false)
            ->sort('seriesTitle', 'ASC')
            ->sort('season', 'ASC')
            ->sort('episode', 'ASC');

        return $builder->getQuery()->execute()->toArray();
    }

    public function getRecentlyWatchedEpisodes(int $limit = 5): array
    {
        $builder = $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->field('watched')->equals(true)
            ->sort('id', 'DESC')
            ->limit($limit);

        return $builder->getQuery()->execute()->toArray();
    }
}
