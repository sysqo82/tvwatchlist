<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\Episode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NextUpController extends AbstractController
{
    public function __construct(
        private readonly Episode $episode
    ) {
    }

    #[Route('/api/nextup', name: 'next_up')]
    public function search(): JsonResponse
    {
        $allUnwatchedEpisodes = $this->episode->getAllUnwatchedEpisodes();
        
        return $this->json([
            'episodes' => $allUnwatchedEpisodes
        ]);
    }
}
