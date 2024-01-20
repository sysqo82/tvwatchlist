<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb\Search;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

class SeriesTitleFactory
{
    public function __construct()
    {
    }

    public function buildFromRequestStack(RequestStack $requestStack): SeriesTitle
    {
        $request = $requestStack->getCurrentRequest() ?? throw new BadRequestException('No request found');

        $searchTerm = $request->get('seriesTitle');
        if ($searchTerm === null) {
            throw new BadRequestException('No series title provided');
        }

        return new SeriesTitle($searchTerm);
    }
}
