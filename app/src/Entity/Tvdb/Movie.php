<?php

declare(strict_types=1);

namespace App\Entity\Tvdb;

class Movie
{
    public const FALLBACK_POSTER = '/build/images/fallback-image.png';

    public readonly string $tvdbId;
    public readonly string $title;
    public readonly string $poster;
    public readonly int $status;
    public readonly string $overview;
    public readonly ?string $releaseDate;
    public readonly ?int $runtime;

    public function __construct(
        string $tvdbId,
        string $title,
        string $poster,
        int $status,
        string $overview = '',
        ?string $releaseDate = null,
        ?int $runtime = null
    ) {
        $this->tvdbId = $tvdbId;
        $this->title = $title;
        $this->poster = $poster;
        $this->status = $status;
        $this->overview = $overview;
        $this->releaseDate = $releaseDate;
        $this->runtime = $runtime;
    }

    /**
     * Returns the poster or the fallback if missing.
     */
    public function getPoster(): string
    {
        return $this->poster !== '' ? $this->poster : self::FALLBACK_POSTER;
    }
}
