<?php

namespace App\Tests\Helper;

use App\Helper\NextUpHelper;
use App\Repository\Episode;
use App\Repository\Series;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class NextUpHelperTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private NextUpHelper $unit;
    private Series $series;
    public function setUp(): void
    {
        $this->series = Mockery::mock(Series::class);

        $this->unit = new NextUpHelper(
            $this->series
        );
    }

    public function testGetSeriesNotOnRecentlyWatchedList(): void
    {
        $this->series->shouldReceive('getLatestTitleFromUniverse')
            ->with('dc')
            ->andReturn('legends of tomorrow');
        $this->series->shouldReceive('getLatestTitleFromUniverse')
            ->with('marvel')
            ->andReturn('daredevil');

        $this->series->shouldReceive('getTitlesRecentlyWatched')
            ->andReturn(['the flash','arrow','jessica jones','schitts creek']);
        $this->series->shouldReceive('getUniverses')
            ->andReturn(['dc','marvel']);
        $this->series->shouldReceive('getTitlesNotRecentlyWatchedAndNotInAnUniverse')
            ->andReturn(['succession']);

        for ($i = 0; $i < 100; $i++) {
            $actual = $this->unit->getSeriesNotOnRecentlyWatchedList();

            $this->assertNotSame('the flash', $actual);
            $this->assertNotSame('arrow', $actual);
            $this->assertNotSame('schitts creek', $actual);
        }
    }

    public function testGetSeriesNotOnRecentlyWatchedListWithNoShows(): void
    {
        $this->series->expects('getUniverses')
            ->andReturns([]);
        $this->series->expects('getTitlesRecentlyWatched')
            ->andReturn([]);
        $this->series->expects('getTitlesNotRecentlyWatchedAndNotInAnUniverse')
            ->andReturn([]);

        $this->assertSame('', $this->unit->getSeriesNotOnRecentlyWatchedList());
    }

    /**
     * @dataProvider getRecentlyWatchedDataProvider
     */
    public function testGetShowFromRecentlyWatchedList($watched, $expected): void
    {
        $this->series->expects('getTitlesWithWatchableEpisodes')
            ->andReturns(['show1', 'show2', 'show3', 'show4', 'show5']);
        $this->series->expects('getTitlesRecentlyWatched')
            ->andReturns($watched);

        $this->assertSame($expected, $this->unit->getSeriesFromRecentlyWatchedList());
    }

    public static function getRecentlyWatchedDataProvider(): array
    {
        return [
            'empty' => [
                'recentlyWatched' => [],
                'expected' => '',
            ],
            'one item in list' => [
                'recentlyWatched' => ['show1'],
                'expected' => 'show1',
            ],
            'only two items in list' => [
                'recentlyWatched' => ['show1', 'show2'],
                'expected' => 'show2',
            ],
            'two same items in list' => [
                'recentlyWatched' => ['show1', 'show1'],
                'expected' => 'show1',
            ],
            'two items but one is not in watchable list' => [
                'recentlyWatched' => ['show1', 'show6'],
                'expected' => 'show1',
            ],
            'three unique items' => [
                'recentlyWatched' => ['show1', 'show2', 'show3'],
                'expected' => 'show3',
            ],
            'four items, two shows, equal spread' => [
                'recentlyWatched' => ['show4', 'show2', 'show2', 'show4'],
                'expected' => 'show2',
            ],
            'four items, two shows, three of one, one of one' => [
                'recentlyWatched' => ['show4', 'show2', 'show4', 'show4'],
                'expected' => 'show2',
            ],
            'four items, three shows' => [
                'recentlyWatched' => ['show4', 'show2', 'show3', 'show4'],
                'expected' => 'show3',
            ],
            'five items, four shows' => [
                'recentlyWatched' => ['show5', 'show2', 'show3', 'show4', 'show5'],
                'expected' => 'show4',
            ],
            'five items, two shows' => [
                'recentlyWatched' => ['show1', 'show2', 'show2', 'show2', 'show1'],
                'expected' => 'show2',
            ],
            'five items, three shows, evenly spread' => [
                'recentlyWatched' => ['show1', 'show2', 'show3', 'show1', 'show2'],
                'expected' => 'show3',
            ],
            'five items, three shows, awkwardly spread' => [
                'recentlyWatched' => ['show1', 'show3', 'show2', 'show2', 'show2'],
                'expected' => 'show2',
            ],
            'five items, three shows, 1-3-2-3-2' => [
                'recentlyWatched' => ['show1', 'show3', 'show2', 'show3', 'show2'],
                'expected' => 'show2',
            ]
        ];
    }
}
