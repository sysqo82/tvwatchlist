<?php

declare(strict_types=1);

namespace App\Processor;

use App\Entity\Tvdb\Episode;
use App\Entity\Tvdb\Series;

class TvdbEpisodeData
{
    public function addEpisodeDataToSeries(Series $series, array $episodesData, $fromEpisode = 1): void
    {
        foreach ($episodesData as $episodeData) {
            if ($episodeData['number'] < $fromEpisode) {
                continue;
            }

            if ($episodeData['aired'] === null) {
                continue;
            }

            $series->addEpisode(new Episode(
                (string) $episodeData['id'],
                $episodeData['name'],
                $episodeData['overview'],
                $episodeData['aired'],
                $episodeData['seasonNumber'],
                $episodeData['number']
            ));
        }
    }
}
