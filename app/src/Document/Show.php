<?php

declare(strict_types=1);

namespace App\Document;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'Show')]
#[MongoDB\Index(keys: ['tvdbSeriesId' => 'asc'], options: ['unique' => true])]
class Show
{
    #[MongoDB\Id]
    public string $id;

    #[MongoDB\Field(type: 'string')]
    public string $tvdbSeriesId;

    #[MongoDB\Field(type: 'string')]
    public string $title;

    #[MongoDB\Field(type: 'string')]
    public string $poster;

    #[MongoDB\Field(type: 'string')]
    public string $status;

    #[MongoDB\Field(type: 'string')]
    public string $platform;

    #[MongoDB\Field(type: 'string')]
    public string $universe;

    #[MongoDB\Field(type: 'date_immutable')]
    public DateTimeImmutable $addedAt;

    #[MongoDB\Field(type: 'date_immutable')]
    public DateTimeImmutable $lastChecked;

    #[MongoDB\Field(type: 'bool')]
    public bool $hasEpisodes = false;

    public function __construct()
    {
    }
}
