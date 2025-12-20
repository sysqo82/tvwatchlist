<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb\Response;

class MovieFactory
{
    public function create(array $movie): ?array
    {
        if (!isset($movie['tvdb_id']) || !isset($movie['name'])) {
            return null;
        }

        return [
            'tvdbId' => (string) $movie['tvdb_id'],
            'title' => $movie['name'],
            'poster' => $movie['image_url'] ?? '',
            'year' => $movie['year'] ?? '',
            'overview' => $movie['overview'] ?? ''
        ];
    }
}
