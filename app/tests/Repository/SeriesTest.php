<?php

namespace App\Tests\Repository;

use App\Document\Episode as EpisodeDocument;
use App\Document\History;
use App\Repository\Series;
use DG\BypassFinals;
use Doctrine\ODM\MongoDB\Aggregation\Aggregation;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Doctrine\ODM\MongoDB\Aggregation\Stage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SeriesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Series $unit;
    private DocumentManager $documentManager;
    private Builder $aggregationBuilder;

    public function setUp(): void
    {
        BypassFinals::enable();

        $this->aggregationBuilder = Mockery::mock(Builder::class);

        $this->documentManager = Mockery::mock(DocumentManager::class);
        $this->documentManager->allows('createAggregationBuilder')
            ->with(EpisodeDocument::class)
            ->andReturn($this->aggregationBuilder)
            ->byDefault();

        $this->unit = new Series($this->documentManager);
    }

    /**
     * @dataProvider getTitlesRecentlyWatchedProvider
     */
    public function testGetTitlesRecentlyWatched(array $expected, array $aggregationResult)
    {
        $this->setUpGetTitlesRecentlyWatchedExpectations($aggregationResult);
        $this->assertSame($expected, $this->unit->getTitlesRecentlyWatched());
    }

    private function setUpGetTitlesRecentlyWatchedExpectations($aggregationResult): void
    {
        $this->documentManager->expects('createAggregationBuilder')
            ->with(History::class)
            ->andReturn($this->aggregationBuilder);

        // $builder->sort('id', 'DESC')->limit(5);
        $sort = Mockery::mock(Stage\Sort::class);
        $this->aggregationBuilder->expects('sort')->with('id', 'DESC')->andReturn($sort);
        $stage = Mockery::mock(Stage::class);
        $sort->expects('limit')->with(5)->andReturn($stage);

        // $builder->getAggregation()->getIterator()->toArray()
        $aggregationMock = Mockery::mock(Aggregation::class);
        $this->aggregationBuilder->expects('getAggregation')->andReturn($aggregationMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $aggregationMock->expects('getIterator')->andReturn($iteratorMock);
        $iteratorMock->expects('toArray')->andReturn($aggregationResult);
    }

    public static function getTitlesRecentlyWatchedProvider(): array
    {
        return [
            'empty' => [
                [],
                []
            ],
            'five' => [
                ['series1', 'series2', 'series3', 'series4', 'series5'],
                [
                    ['seriesTitle' => 'series1'],
                    ['seriesTitle' => 'series2'],
                    ['seriesTitle' => 'series3'],
                    ['seriesTitle' => 'series4'],
                    ['seriesTitle' => 'series5'],
                ]
            ]
        ];
    }

    /**
     * @dataProvider titlesProvider
     */
    public function testGetTitlesWithWatchableEpisodes(array $expected, array $aggregationResult)
    {
        $matchStage = Mockery::mock(Stage\MatchStage::class);

        // $builder->match()->field('watched')->equals(false)
        $this->aggregationBuilder->expects('match')->andReturn($matchStage);
        $matchStage->expects('field')->with('watched')->andReturnSelf();
        $matchStage->expects('equals')->with(false)->andReturnSelf();

        // $builder->group()->field('id')->expression('$seriesTitle');
        $group = Mockery::mock(Stage\Group::class);
        $matchStage->expects('group')->andReturn($group);
        $group->expects('field')->with('id')->andReturnSelf();
        $group->expects('expression')->with('$seriesTitle');

        // $builder->getAggregation()->getIterator()->toArray()
        $aggregationMock = Mockery::mock(Aggregation::class);
        $this->aggregationBuilder->expects('getAggregation')->andReturn($aggregationMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $aggregationMock->expects('getIterator')->andReturn($iteratorMock);
        $iteratorMock->expects('toArray')->andReturn($aggregationResult);
        $this->assertSame($expected, $this->unit->getTitlesWithWatchableEpisodes());
    }

    public static function titlesProvider(): array
    {
        return [
            'empty' => [
                [],
                []
            ],
            'five' => [
                ['series1', 'series2', 'series3', 'series4', 'series5'],
                [
                    ['_id' => 'series1'],
                    ['_id' => 'series2'],
                    ['_id' => 'series3'],
                    ['_id' => 'series4'],
                    ['_id' => 'series5'],
                ]
            ]
        ];
    }

    /**
     * @dataProvider titlesProvider
     */
    public function testGetTitlesNotRecentlyWatchedAndNotInAnUniverse(array $expected, array $aggregationResult): void
    {
        // $builder->match()->field('watched')->equals(false)
        $matchStage = Mockery::mock(Stage\MatchStage::class);
        $this->aggregationBuilder->expects('match')->andReturn($matchStage);
        $matchStage->expects('field')->with('watched')->andReturnSelf();
        $matchStage->expects('equals')->with(false)->andReturnSelf();

        // $builder->match()->field('seriesTitle')->notIn($this->getTitlesRecentlyWatched())
        $this->setUpGetTitlesRecentlyWatchedExpectations([
            ['seriesTitle' => 'series6'],
            ['seriesTitle' => 'series7'],
            ['seriesTitle' => 'series8'],
            ['seriesTitle' => 'series9'],
            ['seriesTitle' => 'series10'],
        ]);
        $matchStage->expects('field')->with('seriesTitle')->andReturnSelf();
        $matchStage->expects('notIn')
            ->with(['series6', 'series7', 'series8', 'series9', 'series10'])
            ->andReturnSelf();

        // $builder->match()->field('universe')->equals('')
        $matchStage->expects('match')->twice()->andReturnSelf();
        $matchStage->expects('field')->with('universe')->andReturnSelf();
        $matchStage->expects('equals')->with('')->andReturnSelf();

        // $builder->group()->field('id')->expression('$seriesTitle');
        $group = Mockery::mock(Stage\Group::class);
        $matchStage->expects('group')->andReturn($group);
        $group->expects('field')->with('id')->andReturnSelf();
        $group->expects('expression')->with('$seriesTitle');

        // $builder->getAggregation()->getIterator()->toArray()
        $aggregationMock = Mockery::mock(Aggregation::class);
        $this->aggregationBuilder->expects('getAggregation')->andReturn($aggregationMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $aggregationMock->expects('getIterator')->andReturn($iteratorMock);
        $iteratorMock->expects('toArray')->andReturn($aggregationResult);
        $this->assertSame(
            $expected,
            $this->unit->getTitlesNotRecentlyWatchedAndNotInAnUniverse()
        );
    }

    /**
     * @dataProvider universesProvider
     */
    public function testGetUniverses(array $expected, array $aggregationResult): void
    {
        // $builder->match()->field('watched')->equals(false)
        $matchStage = Mockery::mock(Stage\MatchStage::class);
        $this->aggregationBuilder->expects('match')->andReturn($matchStage);
        $matchStage->expects('field')->with('watched')->andReturnSelf();
        $matchStage->expects('equals')->with(false)->andReturnSelf();

        // $builder->match()->field('universe')->notEqual('')
        $matchStage->expects('match')->andReturnSelf();
        $matchStage->expects('field')->with('universe')->andReturnSelf();
        $matchStage->expects('notEqual')->with('')->andReturnSelf();

        // $builder->group()->field('id')->expression('$universe');
        $group = Mockery::mock(Stage\Group::class);
        $matchStage->expects('group')->andReturn($group);
        $group->expects('field')->with('id')->andReturnSelf();
        $group->expects('expression')->with('$universe');

        // $builder->getAggregation()->getIterator()->toArray()
        $aggregationMock = Mockery::mock(Aggregation::class);
        $this->aggregationBuilder->expects('getAggregation')->andReturn($aggregationMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $aggregationMock->expects('getIterator')->andReturn($iteratorMock);

        $iteratorMock->expects('toArray')->andReturn($aggregationResult);

        $this->assertSame(
            $expected,
            $this->unit->getUniverses()
        );
    }

    public static function universesProvider(): array
    {
        return [
            'empty' => [
                [],
                []
            ],
            'five' => [
                ['universe1', 'universe2', 'universe3', 'universe4', 'universe5'],
                [
                    ['_id' => 'universe1'],
                    ['_id' => 'universe2'],
                    ['_id' => 'universe3'],
                    ['_id' => 'universe4'],
                    ['_id' => 'universe5'],
                ]
            ]
        ];
    }

    /**
     * @dataProvider latestTitleFromUniverseProvider
     */
    public function testGetLatestTitleFromUniverse(string $expected, array $aggregationResult): void
    {
        // $builder->match()->field('watched')->equals(false)
        $matchStage = Mockery::mock(Stage\MatchStage::class);
        $this->aggregationBuilder->expects('match')->andReturn($matchStage);
        $matchStage->expects('field')->with('watched')->andReturnSelf();
        $matchStage->expects('equals')->with(false)->andReturnSelf();

        // $builder->match()->field('universe')->equals('universe')
        $matchStage->expects('match')->andReturnSelf();
        $matchStage->expects('field')->with('universe')->andReturnSelf();
        $matchStage->expects('equals')->with('universe')->andReturnSelf();

        // $builder->sort('airDate', 'ASC')->limit(1)
        $sortStage = Mockery::mock(Stage\Sort::class);
        $matchStage->expects('sort')->with('airDate', 'ASC')->andReturn($sortStage);
        $stage = Mockery::mock(Stage::class);
        $sortStage->expects('limit')->with(1)->andReturn($stage);

        // $builder->getAggregation()->getIterator()->toArray()
        $aggregationMock = Mockery::mock(Aggregation::class);
        $this->aggregationBuilder->expects('getAggregation')->andReturn($aggregationMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $aggregationMock->expects('getIterator')->andReturn($iteratorMock);


        $iteratorMock->expects('toArray')->andReturn($aggregationResult);

        $this->assertSame(
            $expected,
            $this->unit->getLatestTitleFromUniverse('universe')
        );
    }

    public static function latestTitleFromUniverseProvider(): array
    {
        return [
            'empty' => [
                '',
                [],
            ],
            'first one' => [
                'series1',
                [
                    ['seriesTitle' => 'series1'],
                    ['seriesTitle' => 'series2']
                ]
            ]
        ];
    }

    /**
     * @dataProvider titlesProvider
     */
    public function testGetUnfinishedSeriesTitles(array $expected, array $aggregationResult)
    {
        // $builder->match()->field('status')->notEqual(EpisodeDocument::VALID_STATUSES[EpisodeDocument::STATUS_FINISHED])
        $matchStage = Mockery::mock(Stage\MatchStage::class);
        $this->aggregationBuilder->expects('match')->andReturn($matchStage);
        $matchStage->expects('field')->with('status')->andReturnSelf();
        $matchStage->expects('notEqual')
            ->with(EpisodeDocument::VALID_STATUSES[EpisodeDocument::STATUS_FINISHED])
            ->andReturnSelf();

        // $builder->group()->field('id')->expression('$seriesTitle');
        $group = Mockery::mock(Stage\Group::class);
        $matchStage->expects('group')->andReturn($group);
        $group->expects('field')->with('id')->andReturnSelf();
        $group->expects('expression')->with('$seriesTitle');

        // $builder->getAggregation()->getIterator()->toArray()
        $aggregationMock = Mockery::mock(Aggregation::class);
        $this->aggregationBuilder->expects('getAggregation')->andReturn($aggregationMock);

        $iteratorMock = Mockery::mock(Iterator::class);
        $aggregationMock->expects('getIterator')->andReturn($iteratorMock);
        $iteratorMock->expects('toArray')->andReturn($aggregationResult);

        $this->assertSame(
            $expected,
            $this->unit->getUnfinishedSeriesTitles()
        );
    }
}
