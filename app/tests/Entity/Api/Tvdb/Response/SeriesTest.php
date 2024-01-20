<?php

namespace App\Tests\Entity\Api\Tvdb\Response;

use App\Entity\Api\Tvdb\Response\Series;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SeriesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Series $unit;


    public function testJsonSerialize()
    {
        $this->unit = new Series('1', 'name', 'overview', 'image url', 2020);
        $this->assertSame(
            '{"tvdbId":"1","title":"name","overview":"overview","poster":"image url","year":2020}',
            json_encode($this->unit)
        );
    }
}
