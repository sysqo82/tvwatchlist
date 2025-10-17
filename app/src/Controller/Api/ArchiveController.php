<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ArchivedSeries;
use App\Processor\Ingest;
use App\Entity\Ingest\Criteria;
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
        
        return $this->json([
            'archivedSeries' => $archivedSeries
        ]);
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
}