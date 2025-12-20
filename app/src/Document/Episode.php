<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use DateTimeInterface;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(
    indexes: [
        new ODM\Index(
            keys: ['seriesTitle' => 'asc', 'season' => 'asc', 'episode' => 'asc'],
            unique: true
        )
    ]
)]
#[ODM\HasLifecycleCallbacks]
#[Unique(
    fields: ['seriesTitle', 'season', 'episode'],
    message: 'Series, Season and Episode combination should be unique'
)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: [
        'groups' => ['episode:read'],
        'skip_null_values' => true,
        'allow_extra_attributes' => false
    ],
    denormalizationContext: [
        'groups' => ['episode:write']
    ],
    order: ['airDate' => 'ASC']
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

    #[Groups(['episode:read','identifier'])]
    #[ODM\Id(type: 'integer', strategy: 'INCREMENT')]
    private int $id;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $title;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $description;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'integer')]
    #[Assert\NotBlank]
    public int $season;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'integer')]
    #[Assert\NotBlank]
    public int $episode;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $tvdbEpisodeId;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $seriesTitle;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $tvdbSeriesId;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $poster;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    public string $universe;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\Choice(choices: self::AVAILABLE_PLATFORMS)]
    public string $platform;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\Choice(choices: self::VALID_STATUSES)]
    public string $status;

    #[Groups(['episode:read'])]
    #[ODM\Field(type: 'date')]
    public ?DateTimeInterface $airDate;

    #[Groups(['episode:read','episode:write'])]
    #[ODM\Field(type: 'boolean')]
    public bool $watched = false;

    public function getId(): int
    {
        return $this->id;
    }
}
