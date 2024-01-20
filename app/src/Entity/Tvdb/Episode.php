<?php

declare(strict_types=1);

namespace App\Entity\Tvdb;

readonly class Episode
{
    public string $tvdbId;
    public string $title;
    public string $overview;
    public ?string $aired;
    public int $seasonNumber;
    public int $number;

    public function __construct(
        string $tvdbId,
        string $title,
        string $overview,
        string $aired,
        int $seasonNumber,
        int $number
    ) {
        $this->tvdbId = $tvdbId;
        $this->title = $title;
        $this->overview = $overview;
        $this->aired = $aired;
        $this->seasonNumber = $seasonNumber;
        $this->number = $number;
    }
}
