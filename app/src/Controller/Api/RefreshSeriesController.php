<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DataProvider\TvdbSeriesDataProvider;
use App\Document\Episode;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RefreshSeriesController extends AbstractController
{
    public function __construct(
        private TvdbSeriesDataProvider $tvdbSeriesDataProvider
    ) {
    }

    #[Route('/api/series/{tvdbSeriesId}/refresh', name: 'refresh_series', methods: ['POST'])]
    public function refreshSeries(string $tvdbSeriesId, DocumentManager $documentManager): JsonResponse
    {
        try {
            // Get the updated series data from TVDB
            $series = $this->tvdbSeriesDataProvider->getSeries($tvdbSeriesId);
            
            if ($series === null) {
                return new JsonResponse(['error' => 'Series not found on TVDB'], Response::HTTP_NOT_FOUND);
            }

            $episodeRepository = $documentManager->getRepository(Episode::class);
            
            // Update all episodes for this series with the new poster
            $episodes = $episodeRepository->findBy(['tvdbSeriesId' => $tvdbSeriesId]);
            
            if (empty($episodes)) {
                return new JsonResponse(['error' => 'No episodes found for this series'], Response::HTTP_NOT_FOUND);
            }

            $updatedCount = 0;
            foreach ($episodes as $episode) {
                // Update poster using the getPoster() method which handles fallback
                $episode->poster = $series->getPoster();
                $documentManager->persist($episode);
                $updatedCount++;
            }
            
            $documentManager->flush();

            return new JsonResponse([
                'message' => 'Series refreshed successfully',
                'updatedEpisodes' => $updatedCount,
                'poster' => $series->getPoster()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to refresh series: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
