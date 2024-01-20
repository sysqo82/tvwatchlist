<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Helper\NextUpHelper;
use App\Repository\Episode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NextUpController extends AbstractController
{
    public function __construct(
        private readonly NextUpHelper $nextUpEpisodeHelper,
        private readonly Episode $episode
    ) {
    }

    #[Route('/api/nextup', name: 'next_up')]
    public function search(): JsonResponse
    {
        $seriesTitle = $this->nextUpEpisodeHelper->getSeriesNotOnRecentlyWatchedList();

        if (!$seriesTitle) {
            $seriesTitle = $this->nextUpEpisodeHelper->getSeriesFromRecentlyWatchedList();
        }

        return $seriesTitle
            ? $this->json($this->episode->getLatestUnwatchedFromSeries($seriesTitle) ?? [])
            : $this->json([]);
    }
}
