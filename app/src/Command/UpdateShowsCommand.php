<?php

declare(strict_types=1);

namespace App\Command;

use App\Document\Episode;
use App\Document\Show;
use App\Processor\Ingest;
use App\Entity\Ingest\Criteria;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-shows',
    description: 'Updates all shows by fetching the latest data from TVDB'
)]
class UpdateShowsCommand extends Command
{
    public function __construct(
        private DocumentManager $documentManager,
        private Ingest $ingestProcessor,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Updating Shows from TVDB');

        $showRepository = $this->documentManager->getRepository(Show::class);
        $allShows = $showRepository->findAll();

        $totalShows = count($allShows);
        $io->info("Found {$totalShows} show(s) to update");

        if ($totalShows === 0) {
            $io->success('No shows to update');
            return Command::SUCCESS;
        }

        $io->progressStart($totalShows);

        $updated = 0;
        $failed = 0;
        $newEpisodes = 0;

        foreach ($allShows as $show) {
            try {
                $io->progressAdvance();

                $this->logger->info("Updating show: {$show->title} (TVDB ID: {$show->tvdbSeriesId})");

                $hadEpisodes = $show->hasEpisodes;

                // Find the latest episode for this show to determine where to start
                $episodeRepository = $this->documentManager->getRepository(Episode::class);
                $latestEpisode = $episodeRepository->createQueryBuilder()
                    ->field('tvdbSeriesId')->equals($show->tvdbSeriesId)
                    ->sort('season', 'DESC')
                    ->sort('episode', 'DESC')
                    ->limit(1)
                    ->getQuery()
                    ->getSingleResult();

                $fromSeason = 1;
                $fromEpisode = 1;

                if ($latestEpisode) {
                    $fromSeason = $latestEpisode->season;
                    $fromEpisode = $latestEpisode->episode;
                    $this->logger->info("Latest episode found: S{$fromSeason}E{$fromEpisode}");
                }

                $criteria = new Criteria(
                    $show->tvdbSeriesId,
                    $fromSeason,
                    $fromEpisode,
                    $show->platform,
                    $show->universe
                );

                $result = $this->ingestProcessor->ingest($criteria);

                $updated++;

                if (!$hadEpisodes && $result['episodeCount'] > 0) {
                    $newEpisodes++;
                    $io->writeln("\n✓ New episodes found for: {$show->title} ({$result['episodeCount']} episodes)");
                } elseif ($hadEpisodes && $result['episodeCount'] > 0) {
                    $io->writeln("\n✓ Updated: {$show->title} ({$result['episodeCount']} total episodes)");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->logger->error("Failed to update show {$show->title}: {$e->getMessage()}");
                $io->writeln("\n✗ Failed to update: {$show->title} - {$e->getMessage()}");
            }
        }

        $io->progressFinish();

        $io->success([
            "Update completed!",
            "Total shows: {$totalShows}",
            "Successfully updated: {$updated}",
            "Failed: {$failed}",
            "Shows with new episodes: {$newEpisodes}"
        ]);

        return Command::SUCCESS;
    }
}
