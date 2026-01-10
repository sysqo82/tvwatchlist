<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post()
    ],
    normalizationContext: [
        'groups' => ['history:read'],
        'skip_null_values' => true,
        'allow_extra_attributes' => false
    ],
    denormalizationContext: [
        'groups' => ['history:write']
    ],
    order: ['id' => 'DESC']
)]
/**
 * @codeCoverageIgnore
 */
class History
{
    #[ODM\Id(type: 'int', strategy: 'INCREMENT')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $seriesTitle;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $tvdbSeriesId = null;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $episodeTitle;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $episodeDescription = null;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'int', nullable: true)]
    public ?int $season = null;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'int', nullable: true)]
    public ?int $episode = null;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $episodeId = null;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $movieId = null;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string')]
    public ?string $universe;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'date')]
    #[Assert\NotBlank]
    public DateTimeInterface $airDate;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'date')]
    #[Assert\NotBlank]
    public DateTimeInterface $watchedAt;

    #[Groups(['history:read','history:write'])]
    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $poster = null;
}
