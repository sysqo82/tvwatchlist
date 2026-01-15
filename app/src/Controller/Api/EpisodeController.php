<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\Episode;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EpisodeController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
    }

    #[Route('/api/episodes/{id}', name: 'api_episode_get', methods: ['GET'])]
    public function getEpisode(int $id): JsonResponse
    {
        $episode = $this->documentManager->getRepository(Episode::class)->find($id);
        
        if (!$episode) {
            return $this->json(['error' => 'Episode not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $episode->getId(),
            'seriesTitle' => $episode->seriesTitle,
            'title' => $episode->title,
            'description' => $episode->description,
            'season' => $episode->season,
            'episode' => $episode->episode,
            'airDate' => $episode->airDate,
            'watched' => $episode->watched,
            'platform' => $episode->platform,
        ]);
    }

    #[Route('/api/episodes', name: 'api_episode_get_collection', methods: ['GET'])]
    public function getEpisodes(): JsonResponse
    {
        $episodes = $this->documentManager->getRepository(Episode::class)
            ->findBy([], ['airDate' => 'ASC']);

        $data = array_map(function ($episode) {
            return [
                'id' => $episode->getId(),
                'seriesTitle' => $episode->seriesTitle,
                'title' => $episode->title,
                'description' => $episode->description,
                'season' => $episode->season,
                'episode' => $episode->episode,
                'airDate' => $episode->airDate,
                'watched' => $episode->watched,
                'platform' => $episode->platform,
            ];
        }, $episodes);

        return $this->json($data);
    }

    #[Route('/api/episodes/{id}', name: 'api_episode_patch', methods: ['PATCH'])]
    public function updateEpisode(int $id, Request $request): JsonResponse
    {
        $episode = $this->documentManager->getRepository(Episode::class)->find($id);
        
        if (!$episode) {
            return $this->json(['error' => 'Episode not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Update only provided fields
        if (isset($data['watched'])) {
            $episode->watched = (bool) $data['watched'];
        }
        if (isset($data['platform'])) {
            $episode->platform = $data['platform'];
        }

        $this->documentManager->flush();

        return $this->json([
            'id' => $episode->getId(),
            'seriesTitle' => $episode->seriesTitle,
            'title' => $episode->title,
            'description' => $episode->description,
            'season' => $episode->season,
            'episode' => $episode->episode,
            'watched' => $episode->watched,
            'platform' => $episode->platform,
            'airDate' => $episode->airDate?->format('Y-m-d'),
            'poster' => $episode->poster,
            'tvdbSeriesId' => $episode->tvdbSeriesId,
            'tvdbEpisodeId' => $episode->tvdbEpisodeId,
        ]);
    }

    #[Route('/api/episodes/{id}', name: 'api_episode_delete', methods: ['DELETE'])]
    public function deleteEpisode(int $id): JsonResponse
    {
        $episode = $this->documentManager->getRepository(Episode::class)->find($id);
        
        if (!$episode) {
            return $this->json(['error' => 'Episode not found'], Response::HTTP_NOT_FOUND);
        }

        $this->documentManager->remove($episode);
        $this->documentManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
