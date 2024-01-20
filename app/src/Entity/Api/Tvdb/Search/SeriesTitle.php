<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb\Search;

class SeriesTitle
{
    public function __construct(public readonly string $title)
    {
    }
}
