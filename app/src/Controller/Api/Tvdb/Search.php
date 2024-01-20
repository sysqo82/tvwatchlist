<?php

declare(strict_types=1);

namespace App\Controller\Api\Tvdb;

use App\Api\TvdbQueryClient;
use App\Entity\Api\Tvdb\Response\SeriesFactory;
use App\Entity\Api\Tvdb\Search as SearchEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class Search extends AbstractController
{
    public function __construct(
        private readonly SearchEntity $searchEntity,
        private readonly TvdbQueryClient $tvdbClient,
        private readonly SeriesFactory $seriesFactory
    ) {
    }

    #[Route('/api/tvdb/search/series', name: 'api_tvdb_search_series', methods: ['GET'])]
    public function searchSeries(): JsonResponse
    {
        $searchResults = $this->tvdbClient->search($this->searchEntity->seriesTitle->title);

        $body = json_decode($searchResults->getContent(), true);

        if ($body['status'] !== 'success') {
            return new JsonResponse([
                'message' => 'TVDB Api request was not successful',
                'status' => 500,
                'title' => 'Something went wrong during search'
            ]);
        }

        $series = [];

        foreach ($body['data'] as $show) {
            $series[] = $this->seriesFactory->create($show);
        }

        return new JsonResponse([
            'status' => 200,
            'title' => 'OK',
            'data' => array_values(array_filter($series))
        ]);
    }
}
