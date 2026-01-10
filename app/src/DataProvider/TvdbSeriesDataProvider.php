<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Api\TvdbQueryClient;
use App\Document\Episode as EpisodeDocument;
use App\Entity\Tvdb\Episode;
use App\Entity\Tvdb\Series;
use App\Processor\TvdbEpisodeData;

class TvdbSeriesDataProvider
{
    private const REGULAR_SEASON_TYPE = 1;

    public function __construct(
        private TvdbQueryClient $client,
        private TvdbEpisodeData $episodeDataProcessor,
        private \Psr\Log\LoggerInterface $logger
    ) {
    }

    public function getSeries(
        string $tvdbSeriesId,
        int $fromSeason = 1,
        int $fromEpisode = 1
    ): ?Series {
        $tvdbApiSeriesData = json_decode($this->client->seriesExtended($tvdbSeriesId)->getContent(), true);
        if ($tvdbApiSeriesData['status'] !== 'success') {
            return null;
        }

        $series = new Series(
            $tvdbSeriesId,
            $tvdbApiSeriesData['data']['name'],
            isset($tvdbApiSeriesData['data']['image']) && $tvdbApiSeriesData['data']['image'] !== null
                ? $tvdbApiSeriesData['data']['image']
                : '',
            $tvdbApiSeriesData['data']['status']['id']
        );

        foreach ($tvdbApiSeriesData['data']['seasons'] as $seasonData) {
            $seasonNumber = $seasonData['number'] ?? 0;
            $seasonTypeId = $seasonData['type']['id'] ?? 0;
            $seasonId = $seasonData['id'] ?? 0;
            
            $this->logger->info("Checking season: {$seasonNumber}, type: {$seasonTypeId}, id: {$seasonId}");
            
            if (
                $seasonTypeId !== self::REGULAR_SEASON_TYPE
                || $seasonNumber < $fromSeason
            ) {
                $this->logger->info("Skipping season {$seasonNumber} (type={$seasonTypeId}, required type=" . self::REGULAR_SEASON_TYPE . ")");
                continue;
            }

            $this->logger->info("Fetching episodes for season {$seasonNumber}, seasonId: {$seasonId}");
            $seasonResponse = $this->client->seasonExtended((string) $seasonId);

            $season = json_decode($seasonResponse->getContent(), true);

            if ($season['status'] !== 'success') {
                $this->logger->error("Season API call failed for seasonId: {$seasonData['id']}");
                return null;
            }

            $episodesCount = isset($season['data']['episodes']) ? count($season['data']['episodes']) : 0;
            $this->logger->info("Season {$seasonData['number']} has {$episodesCount} episodes in the API response");
            
            if ($episodesCount === 0) {
                $this->logger->warning("Season {$seasonData['number']} returned 0 episodes from TVDB API");
                $this->logger->debug("Season data: " . json_encode($season['data']));
            }

            $this->episodeDataProcessor->addEpisodeDataToSeries(
                $series,
                $season['data']['episodes'] ?? [],
                $seasonData['number'] === $fromSeason ? $fromEpisode : 1
            );
        }

        return $series;
    }
}
