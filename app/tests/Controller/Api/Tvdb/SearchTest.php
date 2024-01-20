<?php

namespace App\Tests\Controller\Api\Tvdb;

use App\Api\TvdbQueryClient;
use App\Controller\Api\Tvdb\Search;
use App\Entity\Api\Tvdb\Response\Series;
use App\Entity\Api\Tvdb\Response\SeriesFactory;
use App\Entity\Api\Tvdb\Search as SearchEntity;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SearchTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Search $unit;
    private SearchEntity $searchEntity;
    private TvdbQueryClient $tvdbQueryClient;
    private SeriesFactory $seriesFactory;


    public function setUp(): void
    {
        $this->searchEntity = new SearchEntity(new SearchEntity\SeriesTitle('test'));
        $this->tvdbQueryClient = Mockery::mock(TvdbQueryClient::class);
        $this->seriesFactory = Mockery::mock(SeriesFactory::class);

        $this->unit = new Search(
            $this->searchEntity,
            $this->tvdbQueryClient,
            $this->seriesFactory
        );
    }

    public function testSearchSeries(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('getContent')->andReturn(
            json_encode([
                'status' => 'success',
                'data' => [
                    [
                        'id' => 1,
                        'seriesName' => 'test'
                    ],
                    [
                        'id' => 2,
                        'seriesName' => 'test2'
                    ],
                    [
                        'id' => 3,
                        'seriesName' => 'test3'
                    ]
                ]
            ])
        );

        $this->tvdbQueryClient->expects('search')->with('test')->andReturn($response);

        $seriesOneResponse = new Series(
            1,
            'test',
            'overview',
            'poster',
            2005
        );

        $seriesTwoResponse = new Series(
            2,
            'test2',
            'overview2',
            'poster2',
            2006
        );

        $this->seriesFactory->expects('create')
            ->with([
                'id' => 1,
                'seriesName' => 'test'
            ])
            ->andReturn($seriesOneResponse);

        $this->seriesFactory->expects('create')
            ->with([
                'id' => 2,
                'seriesName' => 'test2'
            ])
            ->andReturn($seriesTwoResponse);

        $this->seriesFactory->expects('create')
            ->with([
                'id' => 3,
                'seriesName' => 'test3'
            ])
            ->andReturnNull();

        $this->assertSame(
            json_encode([
                'status' => 200,
                'title' => 'OK',
                'data' => [
                    $seriesOneResponse,
                    $seriesTwoResponse
                ]
            ]),
            $this->unit->searchSeries()->getContent()
        );
    }

    public function testSearchSeriesWithInvalidResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('getContent')->andReturn(
            json_encode([
                'status' => 'failure'
            ])
        );

        $this->tvdbQueryClient->expects('search')->with('test')->andReturn($response);

        $this->assertSame(
            json_encode([
                'message' => 'TVDB Api request was not successful',
                'status' => 500,
                'title' => 'Something went wrong during search'
            ]),
            $this->unit->searchSeries()->getContent()
        );
    }
}
