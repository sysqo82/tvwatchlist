<?php

declare(strict_types=1);

namespace App\Document;

use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document]
#[ODM\Index(keys: ['archivedAt' => 'desc'])]
#[ODM\Index(keys: ['tvdbMovieId' => 'asc'], options: ['unique' => true])]
#[ODM\HasLifecycleCallbacks]
class ArchivedMovie
{
    #[Groups(['archived_movie:read','identifier'])]
    #[ODM\Id(type: 'int', strategy: 'INCREMENT')]
    private int $id;

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $title;

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $tvdbMovieId;

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $poster;

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'string')]
    public ?string $platform = null;

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'string')]
    public ?string $description = null;

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'date')]
    #[Assert\NotNull]
    public DateTimeInterface $archivedAt;

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'string')]
    public string $archiveReason = 'User removed';

    #[Groups(['archived_movie:read'])]
    #[ODM\Field(type: 'bool')]
    public bool $watched = false;

    public function getId(): int
    {
        return $this->id;
    }

    #[ODM\PrePersist]
    public function prePersist(): void
    {
        if (!isset($this->archivedAt)) {
            $this->archivedAt = new \DateTime();
        }
    }
}
