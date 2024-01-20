<?php

namespace App\Tests\Entity\Api\Tvdb\Response;

use App\Entity\Api\Tvdb\Response\Series;
use App\Entity\Api\Tvdb\Response\SeriesFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SeriesFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private SeriesFactory $unit;

    public function setUp(): void
    {
        $this->unit = new SeriesFactory();
    }

    public function testCreateReturnsNullWhenSeriesDoesNotExist(): void
    {
        $this->assertNull($this->unit->create([]));
    }

    public function testCreateReturnsNullWhenShowTypeNotSeries(): void
    {
        $this->assertNull($this->unit->create(['id' => 1, 'type' => 'show']));
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $show, Series $expected): void
    {
        $this->assertSame(json_encode($expected), json_encode($this->unit->create($show)));
    }

    public static function createDataProvider(): array
    {
        return [
            'standard english' => [
                [
                    'tvdb_id' => '1',
                    'type' => 'series',
                    'name' => 'name',
                    'overview' => 'overview',
                    'image_url' => 'image url',
                    'year' => 2020
                ],
                new Series('1', 'name', 'overview', 'image url', 2020),
            ],
            'english translations' => [
                [
                    'tvdb_id' => '1',
                    'type' => 'series',
                    'name' => 'name',
                    'overviews' => [
                        'eng' => 'english overview'
                    ],
                    'image_url' => 'image url',
                    'year' => 2020,
                    'translations' => [
                        'eng' => 'english name'
                    ],
                ],
                new Series('1', 'english name', 'english overview', 'image url', 2020),
            ],
            'no overview' => [
                [
                    'tvdb_id' => '1',
                    'type' => 'series',
                    'name' => 'name',
                    'image_url' => 'image url',
                    'year' => 2020,
                ],
                new Series('1', 'name', 'No overview available', 'image url', 2020),
            ],
            'no year' => [
                [
                    'tvdb_id' => '1',
                    'type' => 'series',
                    'name' => 'name',
                    'overview' => 'overview',
                    'image_url' => 'image url',
                ],
                new Series('1', 'name', 'overview', 'image url', null),
            ],
        ];
    }
}
