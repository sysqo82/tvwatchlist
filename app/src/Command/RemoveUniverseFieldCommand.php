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
    name: 'app:remove-universe-field',
    description: 'Remove the universe field from all MongoDB collections'
)]
class RemoveUniverseFieldCommand extends Command
{
    public function __construct(
        private DocumentManager $documentManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Removing universe field from all collections');

        $collections = [
            'Movie',
            'Show',
            'Episode',
            'ArchivedSeries',
            'ArchivedMovie',
            'History'
        ];

        foreach ($collections as $collectionName) {
            try {
                $collection = $this->documentManager->getClient()
                    ->selectDatabase('api')
                    ->selectCollection($collectionName);
                $result = $collection->updateMany(
                    [],
                    ['$unset' => ['universe' => '']]
                );

                $io->success(sprintf(
                    'Removed universe field from %d documents in %s collection',
                    $result->getModifiedCount(),
                    $collectionName
                ));
            } catch (\Exception $e) {
                $io->warning(sprintf(
                    'Failed to update %s: %s',
                    $collectionName,
                    $e->getMessage()
                ));
            }
        }

        $io->success('Universe field removal completed!');

        return Command::SUCCESS;
    }
}
