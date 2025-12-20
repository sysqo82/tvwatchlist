<?php

declare(strict_types=1);

namespace App\Controller\Api\Tvdb\Movie;

use App\Entity\Ingest\MovieCriteria;
use App\Processor\MovieIngest as MovieIngestProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class Ingest extends AbstractController
{
    public function __construct(
        private readonly MovieCriteria $criteria,
        private readonly MovieIngestProcessor $movieIngestProcess
    ) {
    }

    #[Route(
        '/api/tvdb/movie/ingest',
        name: 'api_tvdb_movie_ingest',
        methods: ['POST']
    )]
    public function handle(): JsonResponse
    {
        try {
            $result = $this->movieIngestProcess->ingest(
                $this->criteria
            );
            
            return new JsonResponse([
                'message' => sprintf(
                    'Movie "%s" was successfully added to your watchlist.',
                    $result['movieTitle']
                ),
                'status' => 202,
                'title' => 'Movie Added',
                'movieId' => $result['movieId']
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
