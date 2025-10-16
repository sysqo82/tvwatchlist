<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\TvdbQueryClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    public function __construct(
        private readonly TvdbQueryClient $tvdbQueryClient
    ) {
    }

    #[Route('/api/series/{tvdbSeriesId}/overview', name: 'series_overview', methods: ['GET'])]
    public function getSeriesOverview(string $tvdbSeriesId): JsonResponse
    {
        try {
            $response = $this->tvdbQueryClient->seriesExtended($tvdbSeriesId);
            $data = $response->toArray();
            
            $overview = $data['data']['overview'] ?? 'No synopsis available';
            
            return $this->json([
                'overview' => $overview
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'overview' => 'No synopsis available'
            ], 500);
        }
    }

    #[Route('/api/series/{tvdbSeriesId}/network', name: 'series_network', methods: ['GET'])]
    public function getSeriesNetwork(string $tvdbSeriesId): JsonResponse
    {
        try {
            $response = $this->tvdbQueryClient->seriesExtended($tvdbSeriesId);
            $data = $response->toArray();
            
            // Extract network information from TVDB data
            $originalNetwork = $data['data']['originalNetwork'] ?? null;
            $latestNetwork = $data['data']['latestNetwork'] ?? null;
            
            // Return the first available network name
            $networkName = null;
            if ($latestNetwork && isset($latestNetwork['name'])) {
                $networkName = $latestNetwork['name'];
            } elseif ($originalNetwork && isset($originalNetwork['name'])) {
                $networkName = $originalNetwork['name'];
            }
            
            // Map UK networks to their streaming platforms
            $mappedNetworkName = $this->mapUkNetworks($networkName);
            
            return $this->json([
                'network' => $mappedNetworkName,
                'originalNetwork' => $originalNetwork,
                'latestNetwork' => $latestNetwork
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'network' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function mapUkNetworks(?string $networkName): ?string
    {
        if (!$networkName) {
            return null;
        }

        // Convert to lowercase for case-insensitive matching
        $lowerNetworkName = strtolower($networkName);
        
        // BBC channels map to BBC iPlayer
        if ($lowerNetworkName === 'bbc one' ||
            $lowerNetworkName === 'bbc two' ||
            $lowerNetworkName === 'bbc three' ||
            $lowerNetworkName === 'bbc four' ||
            $lowerNetworkName === 'bbc' ||
            str_contains($lowerNetworkName, 'bbc one') ||
            str_contains($lowerNetworkName, 'bbc two') ||
            str_contains($lowerNetworkName, 'bbc three') ||
            str_contains($lowerNetworkName, 'bbc four') ||
            str_contains($lowerNetworkName, 'bbc iplayer')) {
            return 'BBC iPlayer';
        }

        // Channel 4 variants map to All4
        if ($lowerNetworkName === 'channel 4' || 
            $lowerNetworkName === 'channel4' ||
            $lowerNetworkName === 'more 4' || 
            $lowerNetworkName === 'more4' ||
            $lowerNetworkName === '4seven' ||
            $lowerNetworkName === '4 seven' || 
            $lowerNetworkName === 'e4' ||
            $lowerNetworkName === 'film4' ||
            $lowerNetworkName === 'film 4' ||
            $lowerNetworkName === '4music' ||
            $lowerNetworkName === '4 music' ||
            $lowerNetworkName === 'all4' ||
            str_contains($lowerNetworkName, 'channel 4') ||
            str_contains($lowerNetworkName, 'channel4') ||
            str_contains($lowerNetworkName, 'more 4') ||
            str_contains($lowerNetworkName, 'more4')) {
            return 'All4';
        }

        // ITV variants map to ITVx
        if ($lowerNetworkName === 'itv1' || 
            $lowerNetworkName === 'itv2' || 
            $lowerNetworkName === 'itv3' || 
            $lowerNetworkName === 'itv4' || 
            $lowerNetworkName === 'itvx' ||
            $lowerNetworkName === 'itv be' ||
            $lowerNetworkName === 'itv' ||
            str_contains($lowerNetworkName, 'itv1') ||
            str_contains($lowerNetworkName, 'itv2') ||
            str_contains($lowerNetworkName, 'itvx')) {
            return 'ITVx';
        }

        // Return original network name if no mapping found
        return $networkName;
    }
}