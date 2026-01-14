<?php

declare(strict_types=1);

namespace App\Document;

use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document]
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

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $seriesTitle;

    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $tvdbSeriesId = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    public string $episodeTitle;

    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $episodeDescription = null;

    #[ODM\Field(type: 'int', nullable: true)]
    public ?int $season = null;

    #[ODM\Field(type: 'int', nullable: true)]
    public ?int $episode = null;

    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $episodeId = null;

    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $movieId = null;

    #[ODM\Field(type: 'date')]
    #[Assert\NotBlank]
    public DateTimeInterface $airDate;

    #[ODM\Field(type: 'date')]
    #[Assert\NotBlank]
    public DateTimeInterface $watchedAt;

    #[ODM\Field(type: 'string', nullable: true)]
    public ?string $poster = null;
}
