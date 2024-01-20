<?php

declare(strict_types=1);

namespace App\Entity\Tvdb;

class Series
{
    public readonly string $tvdbId;
    public readonly string $title;
    public readonly string $poster;
    public readonly int $status;
    private array $episodes = [];

    public function __construct(
        string $tvdbId,
        string $title,
        string $poster,
        int $status
    ) {
        $this->tvdbId = $tvdbId;
        $this->title = $title;
        $this->poster = $poster;
        $this->status = $status;
        $this->episodes = [];
    }

    public function addEpisode(Episode $episode): void
    {
        $this->episodes[$this->convertToArrayKey($episode->seasonNumber, $episode->number)] = $episode;
    }

    public function getEpisodes(): array
    {
        ksort($this->episodes);
        return $this->episodes;
    }

    private function convertToArrayKey(int $seasonNumber, int $episodeNumber): int
    {
        // Use sprintf for string formatting with leading zeros
        $seasonStr = sprintf("%02d", $seasonNumber);
        $episodeStr = sprintf("%02d", $episodeNumber);

        // Concatenate the strings to create the array key
        return (int) ($seasonStr . $episodeStr);
    }
}
