<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb\Response;

class SeriesFactory
{
    public function create(array $show): ?Series
    {
        if (empty($show)) {
            return null;
        }

        if ($show['type'] !== 'series') {
            return null;
        }

        return new Series(
            $show['tvdb_id'],
            $show['translations']['eng'] ?? $show['name'],
            $show['overviews']['eng'] ?? $show['overview'] ?? 'No overview available',
            $show['image_url'] ?? '',
            isset($show['year']) ? (int) $show['year'] : null,
        );
    }
}
