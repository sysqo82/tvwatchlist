<?php

declare(strict_types=1);

namespace App\Processor;

use App\Entity\Tvdb\Episode;
use App\Entity\Tvdb\Series;
use Psr\Log\LoggerInterface;

class TvdbEpisodeData
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function addEpisodeDataToSeries(Series $series, array $episodesData, $fromEpisode = 1): void
    {
        $this->logger->info("Processing " . count($episodesData) . " episodes for series: {$series->title}");
        
        foreach ($episodesData as $episodeData) {
            if ($episodeData['number'] < $fromEpisode) {
                $this->logger->debug("Skipping episode {$episodeData['number']} (before fromEpisode {$fromEpisode})");
                continue;
            }

            // Use a default date for episodes without air dates (far future date to indicate unaired)
            $airDate = $episodeData['aired'] ?? '2099-12-31';
            
            if ($episodeData['aired'] === null) {
                $this->logger->info("Episode {$episodeData['number']} has no air date, using default date");
            }

            $this->logger->info("Adding episode: S{$episodeData['seasonNumber']}E{$episodeData['number']} - {$episodeData['name']}");
            
            $series->addEpisode(new Episode(
                (string) $episodeData['id'],
                $episodeData['name'],
                $episodeData['overview'] ?? 'Overview unavailable',
                $airDate,
                $episodeData['seasonNumber'],
                $episodeData['number']
            ));
        }
    }
}
