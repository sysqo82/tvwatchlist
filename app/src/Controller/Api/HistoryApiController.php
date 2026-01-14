<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\History;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryApiController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
    }

    #[Route('/api/history/{id}', name: 'api_history_get', methods: ['GET'])]
    public function getHistory(int $id): JsonResponse
    {
        $history = $this->documentManager->getRepository(History::class)->find($id);
        
        if (!$history) {
            return $this->json(['error' => 'History not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $history->getId(),
            'seriesTitle' => $history->seriesTitle,
            'episodeTitle' => $history->episodeTitle,
            'season' => $history->season,
            'episode' => $history->episode,
            'watchedAt' => $history->watchedAt,
        ]);
    }

    #[Route('/api/history', name: 'api_history_get_collection', methods: ['GET'])]
    public function getHistories(): JsonResponse
    {
        $histories = $this->documentManager->getRepository(History::class)
            ->findBy([], ['id' => 'DESC']);

        $data = array_map(function ($history) {
            return [
                'id' => $history->getId(),
                'seriesTitle' => $history->seriesTitle,
                'episodeTitle' => $history->episodeTitle,
                'season' => $history->season,
                'episode' => $history->episode,
                'watchedAt' => $history->watchedAt,
            ];
        }, $histories);

        return $this->json($data);
    }

    #[Route('/api/history', name: 'api_history_post', methods: ['POST'])]
    public function createHistory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['seriesTitle']) || !isset($data['episodeTitle']) || 
            !isset($data['season']) || !isset($data['episode'])) {
            return $this->json(
                ['error' => 'Missing required fields'], 
                Response::HTTP_BAD_REQUEST
            );
        }

        $history = new History();
        $history->seriesTitle = $data['seriesTitle'];
        $history->episodeTitle = $data['episodeTitle'];
        $history->season = (int) $data['season'];
        $history->episode = (int) $data['episode'];
        $history->watchedAt = new \DateTimeImmutable();

        $this->documentManager->persist($history);
        $this->documentManager->flush();

        return $this->json([
            'id' => $history->getId(),
            'seriesTitle' => $history->seriesTitle,
            'episodeTitle' => $history->episodeTitle,
            'season' => $history->season,
            'episode' => $history->episode,
            'watchedAt' => $history->watchedAt,
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/histories/{id}', name: 'api_history_delete', methods: ['DELETE'])]
    public function deleteHistory(string $id): Response
    {
        $history = $this->documentManager->getRepository(History::class)->find($id);

        if (!$history) {
            return $this->json(['error' => 'History not found'], Response::HTTP_NOT_FOUND);
        }

        // Find and update the episode to mark as unwatched
        $episode = $this->documentManager->getRepository(\App\Document\Episode::class)->findOneBy([
            'seriesTitle' => $history->seriesTitle,
            'season' => $history->season,
            'episode' => $history->episode,
        ]);

        if ($episode) {
            $episode->watched = false;
            $this->documentManager->persist($episode);
        }

        // Delete the history record
        $this->documentManager->remove($history);
        $this->documentManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
