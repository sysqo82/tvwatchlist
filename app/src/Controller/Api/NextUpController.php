<?php

declare(strict_types=1);

namespace App\Controller\Api;

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
        
        $shows = array_map(function($show) {
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
        
        return $this->json([
            'episodes' => $allUnwatchedEpisodes,
            'shows' => $shows
        ]);
    }
}
