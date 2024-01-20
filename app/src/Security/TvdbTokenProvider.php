<?php

declare(strict_types=1);

namespace App\Security;

use App\Cache\TvdbApiTokenCache;

class TvdbTokenProvider
{
    public function __construct(
        private TvdbApiTokenCache $tvdbApiTokenCache
    ) {
    }

    public function getToken(): string
    {
        return $this->tvdbApiTokenCache->getToken();
    }
}
