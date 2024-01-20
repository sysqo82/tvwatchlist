<?php

namespace App\Tests\Entity\Tvdb;

use App\Entity\Tvdb\Episode;
use App\Entity\Tvdb\Series;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SeriesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Series $unit;

    public function setUp(): void
    {
        $this->unit = new Series(
            'tvdbId',
            'title',
            'poster',
            1
        );
    }

    public function testGetEpisodesReturnsNothingWhenNoEpisodesAdded(): void
    {
        $this->assertEmpty($this->unit->getEpisodes());
    }

    public function testGetEpisodesReturnsEpisodesInExpectedOrder(): void
    {
        $epOne = new Episode(
            'tvdbId 1',
            'title 1',
            'overview 1',
            'today',
            1,
            2
        );
        $epTwo = new Episode(
            'tvdbId 10',
            'title 10',
            'overview 10',
            'the future',
            10,
            3
        );
        $epThree = new Episode(
            'tvdbId 3',
            'title 3',
            'overview 3',
            'the near future',
            3,
            1
        );
        $this->unit->addEpisode($epOne);
        $this->unit->addEpisode($epTwo);
        $this->unit->addEpisode($epThree);

        $this->assertSame([
            102 => $epOne,
            301 => $epThree,
            1003 => $epTwo
        ], $this->unit->getEpisodes());
    }
}
