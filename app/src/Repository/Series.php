<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Episode;
use App\Document\Episode as EpisodeDocument;
use App\Document\History;
use Doctrine\ODM\MongoDB\DocumentManager;

class Series
{
    public function __construct(
        private DocumentManager $documentManager
    ) {
    }

    public function getTitlesRecentlyWatched(): array
    {
        $builder = $this->documentManager->createAggregationBuilder(History::class);
        $builder->sort('id', 'DESC')->limit(5);

        $watched = [];
        foreach ($builder->getAggregation()->getIterator()->toArray() as $result) {
            $watched[] = $result['seriesTitle'];
        }
        return $watched;
    }

    public function getTitlesWithWatchableEpisodes(): array
    {
        $builder = $this->documentManager->createAggregationBuilder(Episode::class);
        $builder->match()->field('watched')->equals(false)
            ->group()->field('id')->expression('$seriesTitle');

        $seriesList = [];
        foreach ($builder->getAggregation()->getIterator()->toArray() as $series) {
            $seriesList[] = $series['_id'];
        }

        return $seriesList;
    }

    public function getTitlesNotRecentlyWatchedAndNotInAnUniverse(): array
    {
        $builder = $this->documentManager->createAggregationBuilder(Episode::class);
        $builder->match()->field('watched')->equals(false)
            ->match()->field('seriesTitle')->notIn($this->getTitlesRecentlyWatched())
            ->match()->field('universe')->equals('')
            ->group()->field('id')->expression('$seriesTitle');

        $seriesList = [];
        foreach ($builder->getAggregation()->getIterator()->toArray() as $series) {
            $seriesList[] = $series['_id'];
        }

        return $seriesList;
    }

    public function getUniverses(): array
    {
        $builder = $this->documentManager->createAggregationBuilder(Episode::class);
        $builder->match()->field('watched')->equals(false)
            ->match()->field('universe')->notEqual('')
            ->group()->field('id')->expression('$universe');

        $universeList = [];
        foreach ($builder->getAggregation()->getIterator()->toArray() as $universe) {
            $universeList[] = $universe['_id'];
        }

        return $universeList;
    }

    public function getLatestTitleFromUniverse(string $universe): string
    {
        $builder = $this->documentManager->createAggregationBuilder(EpisodeDocument::class);
        $builder->match()->field('watched')->equals(false)
            ->match()->field('universe')->equals($universe)
            ->sort('airDate', 'ASC')
            ->limit(1);

        return $builder->getAggregation()->getIterator()->toArray()[0]->seriesTitle ?? '';
    }

    public function getUnfinishedSeriesTitles(): array
    {
        $builder = $this->documentManager->createAggregationBuilder(EpisodeDocument::class);
        $builder->match()->field('status')->notEqual(EpisodeDocument::VALID_STATUSES[EpisodeDocument::STATUS_FINISHED])
            ->group()->field('id')->expression('$seriesTitle');

        $seriesList = [];
        foreach ($builder->getAggregation()->getIterator()->toArray() as $series) {
            $seriesList[] = $series['_id'];
        }
        return $seriesList;
    }
}
