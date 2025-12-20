<?php

declare(strict_types=1);

namespace App\Entity\Api\Tvdb\Search;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

class MovieTitleFactory
{
    public function __construct()
    {
    }

    public function buildFromRequestStack(RequestStack $requestStack): MovieTitle
    {
        $request = $requestStack->getCurrentRequest() ?? throw new BadRequestException('No request found');

        $searchTerm = $request->get('movieTitle');
        if ($searchTerm === null) {
            throw new BadRequestException('No movie title provided');
        }

        return new MovieTitle($searchTerm);
    }
}
