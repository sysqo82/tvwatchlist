<?php

declare(strict_types=1);

namespace App\Entity\Ingest;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class MovieCriteriaFactory
{
    public function __construct()
    {
    }

    public function buildFromRequestStack(RequestStack $requestStack): MovieCriteria
    {
        $request = $requestStack->getCurrentRequest() ?? throw new BadRequestException('No request found');
        $requestBody = json_decode($request->getContent(), true);
        if (!isset($requestBody['movieId'])) {
            throw new BadRequestException('movieId is required');
        }

        return $this->build(
            $requestBody['movieId'],
            $requestBody['platform'] ?? 'Plex',
            strtolower($requestBody['universe'] ?? '')
        );
    }

    public function build(string $movieId, string $platform, string $universe): MovieCriteria
    {
        return new MovieCriteria($movieId, $platform, $universe);
    }
}
