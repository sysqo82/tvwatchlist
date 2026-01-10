<?php

declare(strict_types=1);

namespace App\Command;

use App\Document\Movie;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fix-movie-state',
    description: 'Fix a movie stuck in watched state'
)]
class FixMovieStateCommand extends Command
{
    public function __construct(private readonly DocumentManager $documentManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tvdbMovieId', InputArgument::REQUIRED, 'The TVDB Movie ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tvdbMovieId = $input->getArgument('tvdbMovieId');

        $movieRepository = $this->documentManager->getRepository(Movie::class);
        $movie = $movieRepository->findOneBy(['tvdbMovieId' => $tvdbMovieId]);

        if (!$movie) {
            $output->writeln(sprintf('Movie with TVDB ID %s not found', $tvdbMovieId));
            return Command::FAILURE;
        }

        $movie->watched = false;
        $movie->watchedAt = null;

        $this->documentManager->flush();

        $output->writeln(sprintf('Fixed movie: %s (set to unwatched)', $movie->title));

        return Command::SUCCESS;
    }
}
