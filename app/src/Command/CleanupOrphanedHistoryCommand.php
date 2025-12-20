<?php

declare(strict_types=1);

namespace App\Command;

use App\Document\History;
use App\Document\Movie;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:cleanup-orphaned-history',
    description: 'Remove History entries for movies that no longer exist'
)]
class CleanupOrphanedHistoryCommand extends Command
{
    public function __construct(private readonly DocumentManager $documentManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $historyRepository = $this->documentManager->getRepository(History::class);
        $movieRepository = $this->documentManager->getRepository(Movie::class);
        
        // Find all History entries that have a movieId
        $movieHistories = $historyRepository->createQueryBuilder()
            ->field('movieId')->exists(true)
            ->field('movieId')->notEqual(null)
            ->getQuery()
            ->execute();
        
        $deleted = 0;
        
        foreach ($movieHistories as $history) {
            if ($history->movieId) {
                // Check if the movie still exists
                $movie = $movieRepository->find($history->movieId);
                
                if (!$movie) {
                    $output->writeln(sprintf(
                        'Removing orphaned history entry for movie ID: %s (Title: %s)',
                        $history->movieId,
                        $history->seriesTitle
                    ));
                    
                    $this->documentManager->remove($history);
                    $deleted++;
                }
            }
        }
        
        if ($deleted > 0) {
            $this->documentManager->flush();
            $output->writeln(sprintf('Successfully removed %d orphaned history entries', $deleted));
        } else {
            $output->writeln('No orphaned history entries found');
        }
        
        return Command::SUCCESS;
    }
}
