<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\Episode;
use App\Document\History;
use App\Document\Movie;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/api/histories/{id}', name: 'history_delete', methods: ['DELETE'])]
    public function delete(string $id, DocumentManager $documentManager, LoggerInterface $logger): JsonResponse
    {
        $historyRepository = $documentManager->getRepository(History::class);
        $history = $historyRepository->find((int) $id);
        
        if (!$history) {
            return $this->json(['error' => 'History entry not found'], 404);
        }
        
        $logger->info('HistoryController: Deleting history entry', [
            'historyId' => $history->getId(),
            'episodeId' => $history->episodeId,
            'movieId' => $history->movieId
        ]);
        
        // If it's an episode (has episodeId), mark episode as unwatched
        if ($history->episodeId) {
            $episodeRepository = $documentManager->getRepository(Episode::class);
            $episode = $episodeRepository->find((int) $history->episodeId);
            
            if ($episode) {
                $logger->info('HistoryController: Marking episode as unwatched', [
                    'episodeId' => $episode->getId(),
                    'title' => $episode->title
                ]);
                $episode->watched = false;
                $documentManager->persist($episode);
            } else {
                $logger->warning('HistoryController: Episode not found', [
                    'episodeId' => $history->episodeId
                ]);
            }
        }
        
        // If it's a movie (has movieId), mark movie as unwatched
        if ($history->movieId) {
            $movieRepository = $documentManager->getRepository(Movie::class);
            $movie = $movieRepository->find($history->movieId);
            
            if ($movie) {
                $logger->info('HistoryController: Marking movie as unwatched', [
                    'movieId' => $movie->id,
                    'title' => $movie->title
                ]);
                $movie->watched = false;
                $movie->watchedAt = null;
                $documentManager->persist($movie);
            } else {
                $logger->warning('HistoryController: Movie not found', [
                    'movieId' => $history->movieId
                ]);
            }
        }
        
        // Delete the history entry
        $documentManager->remove($history);
        $documentManager->flush();
        
        $logger->info('HistoryController: Deleted history entry and unwatched content');
        
        return $this->json(['success' => true], 204);
    }
}
