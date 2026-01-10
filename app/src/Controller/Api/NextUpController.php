<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\Movie;
use App\Document\Show;
use App\Repository\Episode;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NextUpController extends AbstractController
{
    #[Route('/api/nextup', name: 'next_up')]
    public function search(DocumentManager $documentManager): JsonResponse
    {
        $episodeRepository = new Episode($documentManager);
        $allUnwatchedEpisodes = $episodeRepository->getAllUnwatchedEpisodes();

        // Get shows without episodes
        $showRepository = $documentManager->getRepository(Show::class);
        $showsWithoutEpisodes = $showRepository->findBy(['hasEpisodes' => false]);

        $shows = array_map(function ($show) {
            return [
                'id' => $show->id,
                'tvdbSeriesId' => $show->tvdbSeriesId,
                'title' => $show->title,
                'poster' => $show->poster,
                'platform' => $show->platform,
                'universe' => $show->universe,
                'hasEpisodes' => false,
                'addedAt' => $show->addedAt->format('Y-m-d H:i:s'),
                'lastChecked' => $show->lastChecked->format('Y-m-d H:i:s')
            ];
        }, $showsWithoutEpisodes);

        // Get unwatched movies
        $movieRepository = $documentManager->getRepository(Movie::class);
        $unwatchedMovies = $movieRepository->findBy(['watched' => false]);

        $movies = array_map(function ($movie) {
            return [
                'id' => $movie->id,
                'tvdbMovieId' => $movie->tvdbMovieId,
                'title' => $movie->title,
                'poster' => $movie->poster,
                'description' => $movie->description,
                'platform' => $movie->platform,
                'universe' => $movie->universe,
                'releaseDate' => $movie->releaseDate?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'addedAt' => $movie->addedAt?->format('Y-m-d H:i:s') ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'type' => 'movie'
            ];
        }, $unwatchedMovies);

        $response = $this->json([
            'episodes' => $allUnwatchedEpisodes,
            'shows' => $shows,
            'movies' => $movies
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
}
