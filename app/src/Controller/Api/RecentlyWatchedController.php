<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\ArchivedMovie;
use App\Document\ArchivedSeries;
use App\Document\History;
use App\Document\Movie;
use App\Repository\Episode;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RecentlyWatchedController extends AbstractController
{
    #[Route('/api/recently-watched', name: 'recently_watched')]
    public function getRecentlyWatched(DocumentManager $documentManager): JsonResponse
    {
        // Get all recently watched content from History
        $historyRepository = $documentManager->getRepository(History::class);
        $recentHistory = $historyRepository->findBy(
            [],
            ['watchedAt' => 'DESC'],
            100
        );

        // Get all archived series tvdbSeriesIds to filter them out
        $archivedSeriesRepository = $documentManager->getRepository(ArchivedSeries::class);
        $archivedSeriesList = $archivedSeriesRepository->findAll();
        $archivedSeriesIds = array_map(fn($archived) => $archived->tvdbSeriesId, $archivedSeriesList);

        // Get all archived movie tvdbMovieIds to filter them out
        $archivedMovieRepository = $documentManager->getRepository(ArchivedMovie::class);
        $archivedMoviesList = $archivedMovieRepository->findAll();
        $archivedMovieIds = array_map(fn($archived) => $archived->tvdbMovieId, $archivedMoviesList);

        // Convert history entries to array format and filter out archived items
        $allWatched = array_values(array_filter(array_map(function ($history) use ($documentManager, $archivedSeriesIds, $archivedMovieIds) {
            $isMovie = $history->episodeTitle === 'Movie';

            // Skip if this is an archived series
            if (!$isMovie && $history->tvdbSeriesId && in_array($history->tvdbSeriesId, $archivedSeriesIds)) {
                return null;
            }

            $description = null;

            // If it's a movie, fetch it and check if it's archived
            if ($isMovie && $history->movieId) {
                $movieRepository = $documentManager->getRepository(Movie::class);
                $movie = $movieRepository->find($history->movieId);
                if ($movie) {
                    // Skip if movie is archived by checking the ArchivedMovie collection
                    if (in_array($movie->tvdbMovieId, $archivedMovieIds)) {
                        return null;
                    }
                    $description = $movie->description;
                }
            }

            return [
                'historyId' => $history->getId(),
                'seriesTitle' => $history->seriesTitle,
                'tvdbSeriesId' => $history->tvdbSeriesId,
                'episodeTitle' => $isMovie ? '(Movie)' : $history->episodeTitle,
                'episodeDescription' => $history->episodeDescription,
                'season' => $history->season,
                'episode' => $history->episode,
                'watchedAt' => $history->watchedAt->format('Y-m-d'),
                'isMovie' => $isMovie,
                'poster' => $history->poster,
                'description' => $description,
                'movieId' => $history->movieId
            ];
        }, $recentHistory)));

        $response = $this->json($allWatched);

        // Prevent caching to ensure fresh data is always served
        $response->setPublic(false);
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }
}
