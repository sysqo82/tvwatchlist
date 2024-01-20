<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\Episode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RemoveSeriesController extends AbstractController
{
    public function __construct(
        private readonly Episode $episodeRepository,
    ) {
    }

    #[Route('/api/series/{tvdbSeriesId}', name: 'remove_series', methods: ['DELETE'])]
    public function removeSeries(string $tvdbSeriesId): JsonResponse
    {
        $this->episodeRepository->deleteEpisodesWithTvdbSeriesId($tvdbSeriesId);
        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }
}
