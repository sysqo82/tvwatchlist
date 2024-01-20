<?php

namespace App\Tests\Processor;

use App\Entity\Tvdb\Episode;
use App\Entity\Tvdb\Series;
use App\Processor\TvdbEpisodeData;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class TvdbEpisodeDataTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TvdbEpisodeData $unit;

    public function setUp(): void
    {
        $this->unit = new TvdbEpisodeData();
    }

    public function testAddEpisodeDataToSeries()
    {
        $series = new Series(
            '123',
            'Test Series',
            'https://www.thetvdb.com/banners/posters/5b3e0b2d9d0c5.jpg',
            1
        );

        $this->unit->addEpisodeDataToSeries(
            $series,
            [
                [
                    'id' => 2,
                    'name' => 'Test Episode 2',
                    'overview' => 'Test Overview 2',
                    'aired' => '2021-01-02',
                    'seasonNumber' => 2,
                    'number' => 1
                ],
                [
                    'id' => 2,
                    'name' => 'Test Episode 2',
                    'overview' => 'Test Overview 2',
                    'aired' => '2021-01-02',
                    'seasonNumber' => 2,
                    'number' => 2
                ],
                [
                    'id' => 2,
                    'name' => 'Test Episode 2',
                    'overview' => 'Test Overview 2',
                    'aired' => null,
                    'seasonNumber' => 2,
                    'number' => 3
                ]
            ],
            2
        );

        $episodes = $series->getEpisodes();
        $this->assertCount(1, $episodes);
        $this->assertEquals(
            new Episode(
                '2',
                'Test Episode 2',
                'Test Overview 2',
                '2021-01-02',
                2,
                2
            ),
            $episodes[202]
        );
    }
}
