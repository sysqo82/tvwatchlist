<?php

declare(strict_types=1);

namespace App\Processor;

use App\DataProvider\TvdbMovieDataProvider;
use App\Document\Episode as EpisodeDocument;
use App\Document\Movie as MovieDocument;
use App\Entity\Ingest\MovieCriteria;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use RuntimeException;

class MovieIngest
{
    public function __construct(
        private DocumentManager $documentManager,
        private TvdbMovieDataProvider $tvdbMovieDataProvider,
        private LoggerInterface $logger
    ) {
    }

    public function ingest(MovieCriteria $criteria): array
    {
        $this->logger->info("Starting ingestion for movie ID: {$criteria->tvdbMovieId}");

        $movie = $this->tvdbMovieDataProvider->getMovie($criteria->tvdbMovieId);

        $this->logger->info("Movie data retrieved: " . ($movie ? $movie->title : 'NULL'));

        if ($movie === null) {
            $this->logger->error("Movie not found for ID: {$criteria->tvdbMovieId}");
            throw new RuntimeException('Movie not found');
        }

        // Save or update the movie record
        $movieRepository = $this->documentManager->getRepository(MovieDocument::class);
        $movieDocument = $movieRepository->findOneBy(['tvdbMovieId' => $criteria->tvdbMovieId]);

        if (!$movieDocument) {
            $movieDocument = new MovieDocument();
            $movieDocument->tvdbMovieId = $criteria->tvdbMovieId;
            $movieDocument->addedAt = new DateTimeImmutable();
        }

        $movieDocument->title = $movie->title;
        $movieDocument->poster = $movie->getPoster();
        $movieDocument->description = $movie->overview;
        $movieDocument->status = EpisodeDocument::VALID_STATUSES[$movie->status] ?? 'upcoming';
        $movieDocument->platform = $criteria->platform;
        $movieDocument->lastChecked = new DateTimeImmutable();
        $movieDocument->runtime = $movie->runtime;

        if ($movie->releaseDate) {
            try {
                $movieDocument->releaseDate = new DateTimeImmutable($movie->releaseDate);
            } catch (\Exception $e) {
                $this->logger->warning("Invalid release date: {$movie->releaseDate}");
            }
        }

        $this->documentManager->persist($movieDocument);
        $this->documentManager->flush();

        $this->logger->info("Movie ingested: {$movie->title}");

        return [
            'movieTitle' => $movie->title,
            'movieId' => $criteria->tvdbMovieId
        ];
    }
}
