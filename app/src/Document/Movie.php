<?php

declare(strict_types=1);

namespace App\Document;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'Movie')]
#[MongoDB\Index(keys: ['tvdbMovieId' => 'asc'], options: ['unique' => true])]
class Movie
{
    #[MongoDB\Id]
    public string $id;

    #[MongoDB\Field(type: 'string')]
    public string $tvdbMovieId;

    #[MongoDB\Field(type: 'string')]
    public string $title;

    #[MongoDB\Field(type: 'string')]
    public string $poster;

    #[MongoDB\Field(type: 'string')]
    public string $description;

    #[MongoDB\Field(type: 'string')]
    public string $status;

    #[MongoDB\Field(type: 'string')]
    public string $platform;

    #[MongoDB\Field(type: 'string')]
    public string $universe;

    #[MongoDB\Field(type: 'date_immutable', nullable: true)]
    public ?DateTimeImmutable $addedAt = null;

    #[MongoDB\Field(type: 'date_immutable', nullable: true)]
    public ?DateTimeImmutable $lastChecked = null;

    #[MongoDB\Field(type: 'date_immutable', nullable: true)]
    public ?DateTimeImmutable $releaseDate = null;

    #[MongoDB\Field(type: 'int', nullable: true)]
    public ?int $runtime = null;

    #[MongoDB\Field(type: 'bool')]
    public bool $watched = false;

    #[MongoDB\Field(type: 'date_immutable', nullable: true)]
    public ?DateTimeImmutable $watchedAt = null;

    public function __construct()
    {
    }
}
