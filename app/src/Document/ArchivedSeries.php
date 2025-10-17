<?php

declare(strict_types=1);

namespace App\Document;


use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(
    indexes: [
        new ODM\Index(
            keys: ['archivedAt' => 'desc']
        ),
        new ODM\Index(
            keys: ['tvdbSeriesId' => 'asc'],
            unique: true
        )
    ]
)]
#[ODM\HasLifecycleCallbacks]
class ArchivedSeries
{
    #[Groups(['archived_series:read','identifier'])]
    #[ODM\Id(type: 'integer', strategy: 'INCREMENT')]
    private int $id;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $seriesTitle;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $tvdbSeriesId;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $poster;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    public ?string $universe = null;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    public ?string $platform = null;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    public ?string $overview = null;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    public ?string $network = null;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'integer')]
    public int $totalEpisodes = 0;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'integer')]
    public int $watchedEpisodes = 0;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'date')]
    #[Assert\NotNull]
    public DateTimeInterface $archivedAt;

    #[Groups(['archived_series:read'])]
    #[ODM\Field(type: 'string')]
    public string $archiveReason = 'User removed';

    public function getId(): int
    {
        return $this->id;
    }

    #[ODM\PrePersist]
    public function setArchivedAt(): void
    {
        if (!isset($this->archivedAt)) {
            $this->archivedAt = new \DateTime();
        }
    }
}