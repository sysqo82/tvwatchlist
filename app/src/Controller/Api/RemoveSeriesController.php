<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\Episode;
use App\Repository\ArchivedSeries;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RemoveSeriesController extends AbstractController
{
    #[Route('/api/series/{tvdbSeriesId}', name: 'remove_series', methods: ['DELETE'])]
    public function removeSeries(string $tvdbSeriesId, DocumentManager $documentManager): JsonResponse
    {
        try {
            $archivedSeriesRepository = new ArchivedSeries($documentManager);
            $episodeRepository = new Episode($documentManager);
            
            // Archive the series before deleting episodes
            $archivedSeriesRepository->archiveSeriesByTvdbId($tvdbSeriesId);
            
            // Delete all episodes for this series
            $episodeRepository->deleteEpisodesWithTvdbSeriesId($tvdbSeriesId);
            
            return new JsonResponse(['message' => 'Series archived successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to archive series: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
