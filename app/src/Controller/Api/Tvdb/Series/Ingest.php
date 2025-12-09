<?php

declare(strict_types=1);

namespace App\Controller\Api\Tvdb\Series;

use App\Entity\Ingest\Criteria;
use App\Processor\Ingest as IngestProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class Ingest extends AbstractController
{
    public function __construct(
        private readonly Criteria $criteria,
        private readonly IngestProcessor $ingestProcess
    ) {
    }

    #[Route(
        '/api/tvdb/series/ingest',
        name: 'api_tvdb_series_ingest',
        methods: ['POST']
    )]
    public function handle(): JsonResponse
    {
        try {
            $result = $this->ingestProcess->ingest(
                $this->criteria
            );
            
            if ($result['episodeCount'] === 0) {
                return new JsonResponse([
                    'message' => sprintf(
                        'Show "%s" was added but has no episodes available in TVDB yet. We will automatically check for updates weekly.',
                        $result['seriesTitle']
                    ),
                    'status' => 202,
                    'title' => 'Show Added (No Episodes)',
                    'hasEpisodes' => false
                ]);
            }
            
            return new JsonResponse([
                'message' => sprintf(
                    'Processing completed for series: %s. Added %d episode(s) from Season: %d, Episode:%d',
                    $result['seriesTitle'],
                    $result['episodeCount'],
                    $this->criteria->season,
                    $this->criteria->episode
                ),
                'status' => 202,
                'title' => 'OK',
                'hasEpisodes' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error: ' . $e->getMessage(),
                'status' => 500,
                'title' => 'Error'
            ], 500);
        }
    }
}
