<?php

namespace App\Tests\Command;

use App\Command\UpdateUnfinishedSeries;
use App\Document\Episode as EpisodeDocument;
use App\Processor\Ingest;
use App\Repository\Episode as EpisodeRepository;
use App\Repository\Series as SeriesRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUnfinishedSeriesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private UpdateUnfinishedSeries $unit;
    /** @var EpisodeRepository|\Mockery\MockInterface */
    private $episodeRepository;
    /** @var SeriesRepository|\Mockery\MockInterface */
    private $seriesRepository;
    /** @var Ingest|\Mockery\MockInterface */
    private $ingestProcess;

    public function setUp(): void
    {
        $this->episodeRepository = Mockery::mock(EpisodeRepository::class);
        $this->seriesRepository = Mockery::mock(SeriesRepository::class);
        $this->ingestProcess = Mockery::mock(Ingest::class);

        $this->unit = new UpdateUnfinishedSeries(
            $this->episodeRepository,
            $this->seriesRepository,
            $this->ingestProcess
        );
    }

    public function testExecute(): void
    {
        $this->seriesRepository->expects('getUnfinishedSeriesTitles')
            ->andReturns([
                'seriesId',
                'seriesId2'
            ]);

        $episode = new EpisodeDocument();
        $episode->tvdbSeriesId = 'tvdbSeriesId';
        $episode->season = 2;
        $episode->episode = 1;
        $episode->platform = 'platform';
        $episode->universe = 'universe';

        $this->episodeRepository->expects('getFirstEpisodeForSeries')
            ->with('seriesId')
            ->andReturns($episode);

        $this->ingestProcess->expects('ingest')
            ->with(
                Mockery::on(function ($criteria) {
                    $this->assertEquals('tvdbSeriesId', $criteria->tvdbSeriesId);
                    $this->assertEquals(2, $criteria->season);
                    $this->assertEquals(1, $criteria->episode);
                    $this->assertEquals('platform', $criteria->platform);
                    $this->assertEquals('universe', $criteria->universe);
                    return true;
                })
            );

        $this->episodeRepository->expects('getFirstEpisodeForSeries')
            ->with('seriesId2')
            ->andReturns(null);

        $input = Mockery::mock(InputInterface::class);
        $input->expects('bind');
        $input->expects('isInteractive');
        $input->expects('hasArgument');
        $input->expects('validate');

        $output = Mockery::mock(OutputInterface::class);
        $output->expects('writeln')->withAnyArgs()->atLeast()->once();

        $this->unit->run($input, $output);
    }
}
