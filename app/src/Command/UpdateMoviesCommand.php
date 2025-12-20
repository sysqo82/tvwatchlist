<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProvider\TvdbMovieDataProvider;
use App\Document\Movie;
use App\Entity\Ingest\MovieCriteria;
use App\Processor\MovieIngest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:update-movies',
    description: 'Update all movies with latest data from TVDB'
)]
class UpdateMoviesCommand extends Command
{
    public function __construct(
        private DocumentManager $documentManager,
        private TvdbMovieDataProvider $tvdbMovieDataProvider,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting movie update...');
        
        $movieRepository = $this->documentManager->getRepository(Movie::class);
        $movies = $movieRepository->findAll();
        
        $output->writeln(sprintf('Found %d movies to update', count($movies)));
        
        foreach ($movies as $movie) {
            $output->writeln(sprintf('Updating movie: %s (ID: %s)', $movie->title, $movie->tvdbMovieId));
            
            try {
                $movieData = $this->tvdbMovieDataProvider->getMovie($movie->tvdbMovieId);
                
                if ($movieData === null) {
                    $output->writeln(sprintf('  ⚠ Movie not found on TVDB: %s', $movie->tvdbMovieId));
                    continue;
                }
                
                // Update movie fields
                $movie->title = $movieData->title;
                $movie->poster = $movieData->getPoster();
                $movie->description = $movieData->overview;
                $movie->runtime = $movieData->runtime;
                $movie->lastChecked = new \DateTimeImmutable();
                
                if ($movieData->releaseDate) {
                    try {
                        $movie->releaseDate = new \DateTimeImmutable($movieData->releaseDate);
                    } catch (\Exception $e) {
                        $this->logger->warning("Invalid release date for movie {$movie->tvdbMovieId}: {$movieData->releaseDate}");
                    }
                }
                
                $this->documentManager->flush();
                $output->writeln(sprintf('  ✓ Updated: %s', $movie->title));
                
            } catch (\Exception $e) {
                $output->writeln(sprintf('  ✗ Error updating movie %s: %s', $movie->title, $e->getMessage()));
                $this->logger->error('Error updating movie', [
                    'movie' => $movie->title,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $output->writeln('Movie update complete!');
        return Command::SUCCESS;
    }
}
