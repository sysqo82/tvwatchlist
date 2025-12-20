<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb\Search;

readonly class MovieTitle
{
    public function __construct(
        public string $title
    ) {
    }
}
