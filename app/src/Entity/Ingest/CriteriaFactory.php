<?php

declare(strict_types=1);

namespace App\Entity\Ingest;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class CriteriaFactory
{
    public function __construct()
    {
    }

    public function buildFromRequestStack(RequestStack $requestStack): Criteria
    {
        $request = $requestStack->getCurrentRequest() ?? throw new BadRequestException('No request found');
        $requestBody = json_decode($request->getContent(), true);
        if (!isset($requestBody['seriesId'])) {
            throw new BadRequestException('seriesId is required');
        }

        return $this->build(
            $requestBody['seriesId'],
            $requestBody['season'] ?? 1,
            $requestBody['episode'] ?? 1,
            $requestBody['platform'] ?? 'Plex'
        );
    }

    public function build(string $seriesId, int $season, int $episode, string $platform): Criteria
    {
        return new Criteria($seriesId, $season, $episode, $platform);
    }
}
