<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ArchivedMovie;
use App\Repository\ArchivedSeries;
use App\Processor\Ingest;
use App\Entity\Ingest\Criteria;
use App\Document\Movie;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArchiveController extends AbstractController
{
    #[Route('/api/archive', name: 'get_archived_series', methods: ['GET'])]
    public function getArchivedSeries(DocumentManager $documentManager): JsonResponse
    {
        $archivedSeriesRepository = new ArchivedSeries($documentManager);
        $archivedSeries = $archivedSeriesRepository->getAllArchivedSeries();
        
        $archivedMovieRepository = new ArchivedMovie($documentManager);
        $archivedMovies = $archivedMovieRepository->getAllArchivedMovies();
        
        $response = $this->json([
            'archivedSeries' => $archivedSeries,
            'archivedMovies' => $archivedMovies
        ]);
        
        // Prevent caching to ensure fresh data is always served
        $response->setPublic(false);
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
        return $response;
    }

    #[Route('/api/archive/{tvdbSeriesId}/restore', name: 'restore_series', methods: ['POST'])]
    public function restoreSeries(string $tvdbSeriesId, DocumentManager $documentManager, Ingest $ingestProcessor): JsonResponse
    {
        try {
            $archivedSeriesRepository = new ArchivedSeries($documentManager);
            
            // Get the archived series first to retrieve its details
            $archivedSeries = $archivedSeriesRepository->getArchivedSeriesByTvdbId($tvdbSeriesId);
            
            if (!$archivedSeries) {
                return new JsonResponse(['error' => 'Series not found in archive'], Response::HTTP_NOT_FOUND);
            }
            
            // Re-import all episodes using the Ingest processor
            $criteria = new Criteria(
                $tvdbSeriesId,
                1, // Start from season 1
                1, // Start from episode 1
                $archivedSeries['platform'] ?? 'Plex',
                $archivedSeries['universe'] ?? ''
            );
            
            $ingestProcessor->ingest($criteria);
            
            // Remove from archive after successful restore
            $restored = $archivedSeriesRepository->restoreSeriesFromArchive($tvdbSeriesId);
            
            if ($restored) {
                return new JsonResponse(['message' => 'Series restored successfully'], Response::HTTP_OK);
            } else {
                return new JsonResponse(['error' => 'Failed to remove from archive'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to restore series: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/archive/{tvdbSeriesId}', name: 'permanently_delete_series', methods: ['DELETE'])]
    public function permanentlyDeleteSeries(string $tvdbSeriesId, DocumentManager $documentManager): JsonResponse
    {
        try {
            $archivedSeriesRepository = new ArchivedSeries($documentManager);
            $deleted = $archivedSeriesRepository->permanentlyDeleteFromArchive($tvdbSeriesId);
            
            if ($deleted) {
                return new JsonResponse(['message' => 'Series permanently deleted'], Response::HTTP_OK);
            } else {
                return new JsonResponse(['error' => 'Series not found in archive'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to delete series: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/archive/movies/{tvdbMovieId}/restore', name: 'restore_movie', methods: ['POST'])]
    public function restoreMovie(string $tvdbMovieId, DocumentManager $documentManager): JsonResponse
    {
        try {
            $archivedMovieRepository = new ArchivedMovie($documentManager);
            
            // Get the archived movie
            $archivedMovieData = $archivedMovieRepository->getArchivedMovieByTvdbId($tvdbMovieId);
            
            if (!$archivedMovieData) {
                return new JsonResponse(['error' => 'Movie not found in archive'], Response::HTTP_NOT_FOUND);
            }
            
            // Create new Movie document from archived data
            $movie = new Movie();
            $movie->title = $archivedMovieData['title'];
            $movie->tvdbMovieId = $archivedMovieData['tvdbMovieId'];
            $movie->poster = $archivedMovieData['poster'];
            $movie->platform = $archivedMovieData['platform'] ?? 'Unknown';
            $movie->description = $archivedMovieData['description'] ?? '';
            $movie->status = 'Released';
            $movie->watched = false; // Always restore as unwatched
            $movie->watchedAt = null;
            $movie->addedAt = new \DateTimeImmutable();
            $movie->lastChecked = new \DateTimeImmutable();
            
            $documentManager->persist($movie);
            $documentManager->flush();
            
            // Remove from archive
            $archivedMovieRepository->removeArchivedMovie($tvdbMovieId);
            
            return new JsonResponse(['message' => 'Movie restored successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to restore movie: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/archive/movies/{tvdbMovieId}', name: 'permanently_delete_movie', methods: ['DELETE'])]
    public function permanentlyDeleteMovie(string $tvdbMovieId, DocumentManager $documentManager): JsonResponse
    {
        try {
            $archivedMovieRepository = new ArchivedMovie($documentManager);
            
            $archivedMovie = $archivedMovieRepository->getArchivedMovieByTvdbId($tvdbMovieId);
            
            if (!$archivedMovie) {
                return new JsonResponse(['error' => 'Movie not found in archive'], Response::HTTP_NOT_FOUND);
            }
            
            $archivedMovieRepository->removeArchivedMovie($tvdbMovieId);
            
            return new JsonResponse(['message' => 'Movie permanently deleted'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to delete movie: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}