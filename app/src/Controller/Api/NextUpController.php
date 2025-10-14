<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Helper\NextUpHelper;
use App\Repository\Episode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NextUpController extends AbstractController
{
    public function __construct(
        private readonly NextUpHelper $nextUpEpisodeHelper,
        private readonly Episode $episode
    ) {
    }

    #[Route('/api/nextup', name: 'next_up')]
    public function search(Request $request): JsonResponse
    {
        $limit = min(100, (int) $request->query->get('limit', 50)); // Max 100 episodes per request
        $offset = max(0, (int) $request->query->get('offset', 0));
        
        $allUnwatchedEpisodes = $this->episode->getAllUnwatchedEpisodes($limit, $offset);
        $totalCount = $this->episode->countAllUnwatchedEpisodes();
        
        return $this->json([
            'episodes' => $allUnwatchedEpisodes,
            'pagination' => [
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'hasMore' => ($offset + $limit) < $totalCount
            ]
        ]);
    }
}
