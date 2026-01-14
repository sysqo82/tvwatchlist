<?php

declare(strict_types=1);

namespace App\Document;

use DateTimeInterface;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document]
#[ODM\Index(keys: ['seriesTitle' => 'asc', 'season' => 'asc', 'episode' => 'asc'], options: ['unique' => true])]
#[ODM\HasLifecycleCallbacks]
#[Unique(
    fields: ['seriesTitle', 'season', 'episode'],
    message: 'Series, Season and Episode combination should be unique'
)]
class Episode
{
    public final const STATUS_AIRING = 1;
    public final const STATUS_FINISHED = 2;
    public final const STATUS_UPCOMING = 3;
    public final const VALID_STATUSES = [
        self::STATUS_AIRING => 'airing', self::STATUS_FINISHED => 'finished', self::STATUS_UPCOMING => 'upcoming'
    ];
    public final const AVAILABLE_PLATFORMS = ['Plex','Netflix','Disney Plus','Amazon Prime'];

    #[ODM\Id(type: 'int', strategy: 'INCREMENT')]
    private int $id;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $title;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $description;

    #[ODM\Field(type: 'int')]
    #[Assert\NotBlank]
    public int $season;

    #[ODM\Field(type: 'int')]
    #[Assert\NotBlank]
    public int $episode;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $tvdbEpisodeId;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $seriesTitle;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $tvdbSeriesId;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $poster;

    #[ODM\Field(type: 'string')]
    #[Assert\Choice(choices: self::AVAILABLE_PLATFORMS)]
    public string $platform;

    #[ODM\Field(type: 'string')]
    #[Assert\Choice(choices: self::VALID_STATUSES)]
    public string $status;

    #[ODM\Field(type: 'date')]
    public ?DateTimeInterface $airDate;

    #[ODM\Field(type: 'bool')]
    public bool $watched = false;

    public function getId(): int
    {
        return $this->id;
    }
}
