<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\History;
use App\Document\Movie;
use App\Repository\ArchivedMovie as ArchivedMovieRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    #[Route('/api/movies/{id}/watched', name: 'movie_mark_watched', methods: ['PATCH'])]
    public function markAsWatched(string $id, DocumentManager $documentManager): JsonResponse
    {
        $movieRepository = $documentManager->getRepository(Movie::class);
        $movie = $movieRepository->find($id);
        
        if (!$movie) {
            return $this->json(['error' => 'Movie not found'], 404);
        }
        
        $movie->watched = true;
        $movie->watchedAt = new \DateTimeImmutable();
        $documentManager->persist($movie);
        $documentManager->flush();
        
        // Add to watch history
        $history = new History();
        $history->seriesTitle = $movie->title;
        $history->episodeTitle = 'Movie';
        $history->movieId = (string) $movie->id;
        $history->airDate = $movie->releaseDate ?: new \DateTimeImmutable();
        $history->watchedAt = $movie->watchedAt;
        $history->poster = $movie->poster;
        
        $documentManager->persist($history);
        $documentManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Movie marked as watched',
            'movie' => [
                'id' => $movie->id,
                'title' => $movie->title,
                'watched' => $movie->watched,
                'watchedAt' => $movie->watchedAt->format('d-m-Y H:i:s')
            ]
        ]);
    }
    
    #[Route('/api/movies/{id}/archive', name: 'movie_archive', methods: ['POST'])]
    public function archive(string $id, DocumentManager $documentManager, ArchivedMovieRepository $archivedMovieRepository): JsonResponse
    {
        $movieRepository = $documentManager->getRepository(Movie::class);
        $movie = $movieRepository->find($id);
        
        if (!$movie) {
            return $this->json(['error' => 'Movie not found'], 404);
        }
        
        // Archive the movie
        $archivedMovieRepository->archiveMovie($movie);
        
        // Remove any history entries for this movie
        $historyRepository = $documentManager->getRepository(History::class);
        $historyEntries = $historyRepository->findBy(['movieId' => (string) $movie->id]);
        
        foreach ($historyEntries as $historyEntry) {
            $documentManager->remove($historyEntry);
        }
        
        // Remove the movie from the main watchlist
        $documentManager->remove($movie);
        $documentManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Movie archived'
        ]);
    }
    
    #[Route('/api/movies/{id}/unwatch', name: 'movie_unwatch', methods: ['PATCH'])]
    public function unwatch(string $id, DocumentManager $documentManager): JsonResponse
    {
        $movieRepository = $documentManager->getRepository(Movie::class);
        $movie = $movieRepository->find($id);
        
        if (!$movie) {
            return $this->json(['error' => 'Movie not found'], 404);
        }
        
        // Set movie to unwatched
        $movie->watched = false;
        $movie->watchedAt = null;
        $documentManager->persist($movie);
        $documentManager->flush();
        
        // Remove from watch history
        $historyRepository = $documentManager->getRepository(History::class);
        $historyEntries = $historyRepository->findBy(['movieId' => (string) $movie->id]);
        
        foreach ($historyEntries as $historyEntry) {
            $documentManager->remove($historyEntry);
        }
        $documentManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Movie unwatched and returned to watchlist'
        ]);
    }
    
    #[Route('/api/movies/{id}', name: 'movie_delete', methods: ['DELETE'])]
    public function delete(string $id, DocumentManager $documentManager): JsonResponse
    {
        $movieRepository = $documentManager->getRepository(Movie::class);
        $movie = $movieRepository->find($id);
        
        if (!$movie) {
            return $this->json(['error' => 'Movie not found'], 404);
        }
        
        $documentManager->remove($movie);
        $documentManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Movie removed from watchlist'
        ]);
    }
}
