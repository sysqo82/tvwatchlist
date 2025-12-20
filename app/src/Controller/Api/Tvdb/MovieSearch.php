<?php

declare(strict_types=1);

namespace App\Controller\Api\Tvdb;

use App\Api\TvdbQueryClient;
use App\Entity\Api\Tvdb\Response\MovieFactory;
use App\Entity\Api\Tvdb\Search\MovieTitleFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class MovieSearch extends AbstractController
{
    public function __construct(
        private readonly TvdbQueryClient $tvdbClient,
        private readonly MovieFactory $movieFactory,
        private readonly MovieTitleFactory $movieTitleFactory,
        private readonly RequestStack $requestStack
    ) {
    }

    #[Route('/api/tvdb/search/movies', name: 'api_tvdb_search_movies', methods: ['GET'])]
    public function searchMovies(): JsonResponse
    {
        try {
            $movieTitle = $this->movieTitleFactory->buildFromRequestStack($this->requestStack);
            $searchResults = $this->tvdbClient->searchMovies($movieTitle->title);

            $body = json_decode($searchResults->getContent(), true);

            if ($body['status'] !== 'success') {
                return new JsonResponse([
                    'message' => 'TVDB Api request was not successful',
                    'status' => 500,
                    'title' => 'Something went wrong during search'
                ]);
            }

            $movies = [];

            foreach ($body['data'] as $movie) {
                $movieData = $this->movieFactory->create($movie);
                if ($movieData !== null) {
                    $movies[] = $movieData;
                }
            }

            return new JsonResponse([
                'status' => 200,
                'title' => 'OK',
                'data' => $movies
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
