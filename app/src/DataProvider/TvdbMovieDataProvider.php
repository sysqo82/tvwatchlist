<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Api\TvdbQueryClient;
use App\Entity\Tvdb\Movie;
use Psr\Log\LoggerInterface;

class TvdbMovieDataProvider
{
    public function __construct(
        private TvdbQueryClient $client,
        private LoggerInterface $logger
    ) {
    }

    public function getMovie(string $tvdbMovieId): ?Movie
    {
        $this->logger->info("Fetching movie data for TVDB ID: {$tvdbMovieId}");

        $tvdbApiMovieData = json_decode($this->client->movieExtended($tvdbMovieId)->getContent(), true);

        if ($tvdbApiMovieData['status'] !== 'success') {
            $this->logger->error("Movie API call failed for movieId: {$tvdbMovieId}");
            return null;
        }

        $data = $tvdbApiMovieData['data'];
        $this->logger->info("Movie data retrieved: " . $data['name']);

        // Try to get English translation for overview
        $description = '';
        if (isset($data['overviewTranslations']) && in_array('eng', $data['overviewTranslations'])) {
            try {
                $translationResponse = json_decode($this->client->movieTranslations($tvdbMovieId, 'eng')->getContent(), true);
                if ($translationResponse['status'] === 'success' && isset($translationResponse['data']['overview'])) {
                    $description = $translationResponse['data']['overview'];
                    $this->logger->info("English overview found via translations endpoint");
                }
            } catch (\Exception $e) {
                $this->logger->warning("Could not fetch English translations: " . $e->getMessage());
            }
        }

        // Fallback to direct overview field if it exists
        if (empty($description)) {
            $description = $data['overview'] ?? '';
        }

        $this->logger->info("Description found: " . ($description ? substr($description, 0, 100) . '...' : 'NO'));

        $movie = new Movie(
            $tvdbMovieId,
            $data['name'],
            isset($data['image']) && $data['image'] !== null ? $data['image'] : '',
            $data['status']['id'] ?? 1,
            $description,
            $data['first_release']['date'] ?? null,
            $data['runtime'] ?? null
        );

        return $movie;
    }
}
