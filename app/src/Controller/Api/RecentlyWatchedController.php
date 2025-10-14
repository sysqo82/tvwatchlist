<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\Episode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RecentlyWatchedController extends AbstractController
{
    public function __construct(
        private readonly Episode $episode
    ) {
    }

    #[Route('/api/recently-watched', name: 'recently_watched')]
    public function getRecentlyWatched(): JsonResponse
    {
        $recentlyWatchedEpisodes = $this->episode->getRecentlyWatchedEpisodes();
        return $this->json($recentlyWatchedEpisodes);
    }
}