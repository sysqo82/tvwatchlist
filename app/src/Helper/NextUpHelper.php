<?php

declare(strict_types=1);

namespace App\Helper;

use App\Repository\Series;

class NextUpHelper
{
    public function __construct(
        private Series $series
    ) {
    }

    public function getSeriesNotOnRecentlyWatchedList(): string
    {
        $seriesList = [];
        $recentlyWatched = $this->series->getTitlesRecentlyWatched();

        // Get the next unwatched show, not recently watched from each universe and add to the list.
        foreach ($this->series->getUniverses() as $universe) {
            $seriesTitle = $this->series->getLatestTitleFromUniverse($universe);
            if (!in_array($seriesTitle, $recentlyWatched)) {
                $seriesList[] = $seriesTitle;
            }
        }

        // Get all shows that are not recently watched and not in a universe.
        foreach ($this->series->getTitlesNotRecentlyWatchedAndNotInAnUniverse() as $seriesTitle) {
            $seriesList[] = $seriesTitle;
        }

        return empty($seriesList) ? '' : $seriesList[array_rand($seriesList)];
    }

    public function getSeriesFromRecentlyWatchedList(): string
    {
        $watchableSeries = $this->series->getTitlesWithWatchableEpisodes();

        $recentlyWatchedSeries = $this->series->getTitlesRecentlyWatched();

        $recentlyWatchedSeries = array_intersect($recentlyWatchedSeries, $watchableSeries);
        $recentlyWatchedCount = count($recentlyWatchedSeries);

        if ($recentlyWatchedCount === 0) {
            return '';
        }

        $showCounts = array_count_values($recentlyWatchedSeries);

        // If there is only one show in the list, return it.
        // If there is more than one show in the list and they are all the same, return it.
        if ($recentlyWatchedCount === 1 || count($showCounts) === 1) {
            return $recentlyWatchedSeries[0];
        }

        // If there are only two shows in the list, return the one that is not the most recent.
        if (count($showCounts) === 2) {
            unset($showCounts[$recentlyWatchedSeries[0]]);
            return array_keys($showCounts)[0];
        }

        // Remove all instances of the first show in the list.
        $filteredShows = array_values(array_diff($recentlyWatchedSeries, [$recentlyWatchedSeries[0]]));

        // Return the last unique show in the list.
        $upNext = $filteredShows[0];
        $seenBefore = [$filteredShows[0]];
        $filteredShowCount = count($filteredShows);
        for ($i = 1; $i < $filteredShowCount; $i++) {
            if (!in_array($filteredShows[$i], $seenBefore)) {
                $upNext = $filteredShows[$i];
                $seenBefore[] = $filteredShows[$i];
            }
        }

        return $upNext;
    }
}
