<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\TvdbQueryClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    public function __construct(
        private readonly TvdbQueryClient $tvdbQueryClient
    ) {
    }

    #[Route('/api/series/{tvdbSeriesId}/overview', name: 'series_overview', methods: ['GET'])]
    public function getSeriesOverview(string $tvdbSeriesId): JsonResponse
    {
        try {
            $response = $this->tvdbQueryClient->seriesExtended($tvdbSeriesId);
            $data = $response->toArray();
            
            $overview = $data['data']['overview'] ?? 'No synopsis available';
            
            return $this->json([
                'overview' => $overview
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'overview' => 'No synopsis available'
            ], 500);
        }
    }
}