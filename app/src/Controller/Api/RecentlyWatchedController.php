<?php

declare(strict_types=1);

namespace App\Controller\Api;

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
            10
        );
        
        // Convert history entries to array format
        $allWatched = array_map(function($history) use ($documentManager) {
            $isMovie = $history->episodeTitle === 'Movie';
            $description = null;
            
            // If it's a movie, fetch the description
            if ($isMovie && $history->movieId) {
                $movieRepository = $documentManager->getRepository(Movie::class);
                $movie = $movieRepository->find($history->movieId);
                if ($movie) {
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
        }, $recentHistory);
        
        return $this->json($allWatched);
    }
}