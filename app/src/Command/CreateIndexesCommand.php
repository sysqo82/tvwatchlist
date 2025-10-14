<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-indexes',
    description: 'Create MongoDB indexes for better performance',
)]
class CreateIndexesCommand extends Command
{
    public function __construct(
        private DocumentManager $documentManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $collection = $this->documentManager->getDocumentCollection('App\Document\Episode');
            
            // Index for watched status
            $collection->createIndex(['watched' => 1]);
            $io->success('Created index on watched field');
            
            // Index for series queries
            $collection->createIndex(['seriesTitle' => 1, 'season' => 1, 'episode' => 1]);
            $io->success('Created compound index on seriesTitle, season, episode');
            
            // Index for unwatched episodes sorting
            $collection->createIndex(['watched' => 1, 'seriesTitle' => 1, 'season' => 1, 'episode' => 1]);
            $io->success('Created compound index for unwatched episodes');
            
            // Index for recently watched sorting
            $collection->createIndex(['watched' => 1, 'id' => -1]);
            $io->success('Created index for recently watched episodes');

            $io->success('All database indexes created successfully!');
            
        } catch (\Exception $e) {
            $io->error('Failed to create indexes: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}