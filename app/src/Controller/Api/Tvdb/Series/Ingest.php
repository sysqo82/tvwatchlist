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
        $this->ingestProcess->ingest(
            $this->criteria
        );

        return new JsonResponse([
            'message' => sprintf(
                'Processing started for series: %s from Season: %d, Episode:%d',
                $this->criteria->tvdbSeriesId,
                $this->criteria->season,
                $this->criteria->episode
            ),
            'status' => 202,
            'title' => 'OK'
        ]);
    }
}
