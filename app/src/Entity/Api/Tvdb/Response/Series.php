<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb\Response;

use JsonSerializable;

/**
 * Represents a series returned by the TVDB API search endpoint.
 */
readonly class Series implements JsonSerializable
{
    public function __construct(
        public string $tvdbId,
        public string $title,
        public string $overview,
        public string $poster,
        public ?int $year
    ) {
    }

    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
