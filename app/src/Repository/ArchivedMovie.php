<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\ArchivedMovie as ArchivedMovieDocument;
use App\Document\Movie;
use Doctrine\ODM\MongoDB\DocumentManager;

class ArchivedMovie
{
    public function __construct(private readonly DocumentManager $documentManager)
    {
    }

    public function getAllArchivedMovies(): array
    {
        $builder = $this->documentManager->createQueryBuilder(ArchivedMovieDocument::class)
            ->sort('archivedAt', 'DESC');

        $results = $builder->getQuery()->execute()->toArray();

        return array_map(function (ArchivedMovieDocument $movie) {
            return [
                'id' => $movie->getId(),
                'title' => $movie->title,
                'tvdbMovieId' => $movie->tvdbMovieId,
                'poster' => $movie->poster,
                'platform' => $movie->platform,
                'description' => $movie->description,
                'watched' => $movie->watched,
                'archivedAt' => $movie->archivedAt->format('Y-m-d H:i:s'),
                'archiveReason' => $movie->archiveReason
            ];
        }, $results);
    }

    public function archiveMovie(Movie $movie): void
    {
        $archivedMovie = new ArchivedMovieDocument();
        $archivedMovie->title = $movie->title;
        $archivedMovie->tvdbMovieId = $movie->tvdbMovieId;
        $archivedMovie->poster = $movie->poster;
        $archivedMovie->platform = $movie->platform ?? null;
        $archivedMovie->description = $movie->description ?? null;
        $archivedMovie->watched = $movie->watched;

        $this->documentManager->persist($archivedMovie);
        $this->documentManager->flush();
    }

    public function removeArchivedMovie(string $tvdbMovieId): void
    {
        $archivedMovie = $this->documentManager->createQueryBuilder(ArchivedMovieDocument::class)
            ->field('tvdbMovieId')->equals($tvdbMovieId)
            ->getQuery()
            ->getSingleResult();

        if ($archivedMovie) {
            $this->documentManager->remove($archivedMovie);
            $this->documentManager->flush();
        }
    }

    public function getArchivedMovieByTvdbId(string $tvdbMovieId): ?array
    {
        $archivedMovie = $this->documentManager->createQueryBuilder(ArchivedMovieDocument::class)
            ->field('tvdbMovieId')->equals($tvdbMovieId)
            ->getQuery()
            ->getSingleResult();

        if (!$archivedMovie) {
            return null;
        }

        return [
            'id' => $archivedMovie->getId(),
            'title' => $archivedMovie->title,
            'tvdbMovieId' => $archivedMovie->tvdbMovieId,
            'poster' => $archivedMovie->poster,
            'platform' => $archivedMovie->platform,
            'description' => $archivedMovie->description,
            'watched' => $archivedMovie->watched,
            'archivedAt' => $archivedMovie->archivedAt->format('Y-m-d H:i:s')
        ];
    }
}
