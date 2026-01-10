<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\ArchivedSeries as ArchivedSeriesDocument;
use App\Document\Episode as EpisodeDocument;

class ArchivedSeries
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {
    }

    public function getAllArchivedSeries(): array
    {
        $builder = $this->documentManager->createQueryBuilder(ArchivedSeriesDocument::class)
            ->sort('archivedAt', 'DESC');

        return $builder->getQuery()->execute()->toArray();
    }

    public function archiveSeriesByTvdbId(string $tvdbSeriesId): void
    {
        // Get series information from the first episode
        $firstEpisode = $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->field('tvdbSeriesId')->equals($tvdbSeriesId)
            ->sort('season', 'ASC')
            ->sort('episode', 'ASC')
            ->limit(1)
            ->getQuery()
            ->execute()
            ->toArray()[0] ?? null;

        if (!$firstEpisode) {
            throw new \Exception("No episodes found for series ID: $tvdbSeriesId");
        }

        // Count total and watched episodes
        $totalEpisodes = $this->countEpisodesByTvdbSeriesId($tvdbSeriesId);
        $watchedEpisodes = $this->countWatchedEpisodesByTvdbSeriesId($tvdbSeriesId);

        // Create archived series record
        $archivedSeries = new ArchivedSeriesDocument();
        $archivedSeries->seriesTitle = $firstEpisode->seriesTitle;
        $archivedSeries->tvdbSeriesId = $tvdbSeriesId;
        $archivedSeries->poster = $firstEpisode->poster;
        $archivedSeries->universe = $firstEpisode->universe ?? null;
        $archivedSeries->platform = $firstEpisode->platform ?? null;
        $archivedSeries->totalEpisodes = $totalEpisodes;
        $archivedSeries->watchedEpisodes = $watchedEpisodes;

        $this->documentManager->persist($archivedSeries);
        $this->documentManager->flush();
    }

    public function restoreSeriesFromArchive(string $tvdbSeriesId): bool
    {
        $archivedSeries = $this->documentManager->createQueryBuilder(ArchivedSeriesDocument::class)
            ->field('tvdbSeriesId')->equals($tvdbSeriesId)
            ->getQuery()
            ->execute()
            ->toArray()[0] ?? null;

        if ($archivedSeries) {
            $this->documentManager->remove($archivedSeries);
            $this->documentManager->flush();
            return true;
        }

        return false;
    }

    public function getArchivedSeriesByTvdbId(string $tvdbSeriesId): ?array
    {
        $archivedSeries = $this->documentManager->createQueryBuilder(ArchivedSeriesDocument::class)
            ->field('tvdbSeriesId')->equals($tvdbSeriesId)
            ->getQuery()
            ->execute()
            ->toArray()[0] ?? null;

        if ($archivedSeries) {
            return [
                'tvdbSeriesId' => $archivedSeries->tvdbSeriesId,
                'seriesTitle' => $archivedSeries->seriesTitle,
                'platform' => $archivedSeries->platform,
                'universe' => $archivedSeries->universe,
                'poster' => $archivedSeries->poster,
                'overview' => $archivedSeries->overview,
                'network' => $archivedSeries->network,
                'totalEpisodes' => $archivedSeries->totalEpisodes,
                'watchedEpisodes' => $archivedSeries->watchedEpisodes,
                'archivedAt' => $archivedSeries->archivedAt,
                'archiveReason' => $archivedSeries->archiveReason
            ];
        }

        return null;
    }

    public function permanentlyDeleteFromArchive(string $tvdbSeriesId): bool
    {
        $archivedSeries = $this->documentManager->createQueryBuilder(ArchivedSeriesDocument::class)
            ->field('tvdbSeriesId')->equals($tvdbSeriesId)
            ->getQuery()
            ->execute()
            ->toArray()[0] ?? null;

        if ($archivedSeries) {
            $this->documentManager->remove($archivedSeries);
            $this->documentManager->flush();
            return true;
        }

        return false;
    }

    private function countEpisodesByTvdbSeriesId(string $tvdbSeriesId): int
    {
        return $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->field('tvdbSeriesId')->equals($tvdbSeriesId)
            ->count()
            ->getQuery()
            ->execute();
    }

    private function countWatchedEpisodesByTvdbSeriesId(string $tvdbSeriesId): int
    {
        return $this->documentManager->createQueryBuilder(EpisodeDocument::class)
            ->field('tvdbSeriesId')->equals($tvdbSeriesId)
            ->field('watched')->equals(true)
            ->count()
            ->getQuery()
            ->execute();
    }
}
