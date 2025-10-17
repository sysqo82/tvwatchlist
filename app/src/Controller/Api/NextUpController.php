<?php

declare(strict_types=1);

namespace App\Controller\Api;

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
        
        return $this->json([
            'episodes' => $allUnwatchedEpisodes
        ]);
    }
}
