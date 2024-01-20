<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb;

use App\Entity\Api\Tvdb\Search\SeriesTitle;

readonly class Search
{
    public function __construct(
        public SeriesTitle $seriesTitle
    ) {
    }
}
