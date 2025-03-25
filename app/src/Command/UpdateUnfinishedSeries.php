<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Ingest\Criteria;
use App\Processor\Ingest;
use App\Repository\Episode;
use App\Repository\Series;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:ingest-ongoing',
    description: 'Ingest ongoing series',
)]
class UpdateUnfinishedSeries extends Command
{
    public function __construct(
        private Episode $episodeRepository,
        private Series $seriesRespository,
        private Ingest $ingestProcess
    ) {
        parent::__construct();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Find all series that are currently airing
        $ongoingSeries = $this->seriesRespository->getUnfinishedSeriesTitles();

        foreach ($ongoingSeries as $series) {
            $output->writeln(sprintf("Getting episodes for %s", $series));
            // Find the first episode for the series that we've already ingested
            $firstEpisode = $this->episodeRepository->getFirstEpisodeForSeries($series);
            if ($firstEpisode === null) {
                continue;
            }

            // Build the criteria for the series
            $criteria = new Criteria(
                $firstEpisode->tvdbSeriesId,
                $firstEpisode->season,
                $firstEpisode->episode,
                $firstEpisode->platform,
                $firstEpisode->universe
            );

            //Ingest the series
            $this->ingestProcess->ingest($criteria);
            $output->writeln(sprintf("Ingested %s", $series));
        }

        return Command::SUCCESS;
    }
}
