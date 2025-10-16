<?php

declare(strict_types=1);

namespace App\Entity\Ingest;

readonly class Criteria
{
    public function __construct(
        public string $tvdbSeriesId,
        public int $season,
        public int $episode,
        public string $platform = '',
        public string $universe = ''
    ) {
    }
}
