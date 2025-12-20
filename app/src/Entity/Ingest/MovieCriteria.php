<?php

declare(strict_types=1);

namespace App\Entity\Ingest;

readonly class MovieCriteria
{
    public function __construct(
        public string $tvdbMovieId,
        public string $platform = '',
        public string $universe = ''
    ) {
    }
}
