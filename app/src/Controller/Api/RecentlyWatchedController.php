<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\Episode;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RecentlyWatchedController extends AbstractController
{
    #[Route('/api/recently-watched', name: 'recently_watched')]
    public function getRecentlyWatched(DocumentManager $documentManager): JsonResponse
    {
        $episodeRepository = new Episode($documentManager);
        $recentlyWatchedEpisodes = $episodeRepository->getRecentlyWatchedEpisodes();
        return $this->json($recentlyWatchedEpisodes);
    }
}