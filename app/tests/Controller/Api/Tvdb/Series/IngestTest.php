<?php

namespace App\Tests\Controller\Api\Tvdb\Series;

use App\Controller\Api\Tvdb\Series\Ingest;
use App\Entity\Ingest\Criteria;
use App\Processor\Ingest as IngestProcessor;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class IngestTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandle()
    {
        $criteria = new Criteria('123', 1, 1, 'platform', 'universe');
        $ingestProcess = Mockery::mock(IngestProcessor::class);
        $ingestProcess->expects('ingest')
            ->once()
            ->with($criteria)
            ->andReturn([
                'episodeCount' => 0,
                'seriesTitle' => 'Test Series'
            ]);

        $ingest = new Ingest($criteria, $ingestProcess);
        $response = $ingest->handle();

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(202, $responseData['status']);
        $this->assertEquals('Show Added (No Episodes)', $responseData['title']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('hasEpisodes', $responseData);
        $this->assertFalse($responseData['hasEpisodes']);
    }

    public function testHandleWithEpisodes()
    {
        $criteria = new Criteria('123', 1, 1, 'platform', 'universe');
        $ingestProcess = Mockery::mock(IngestProcessor::class);
        $ingestProcess->expects('ingest')
            ->once()
            ->with($criteria)
            ->andReturn([
                'episodeCount' => 5,
                'seriesTitle' => 'Test Series'
            ]);

        $ingest = new Ingest($criteria, $ingestProcess);
        $response = $ingest->handle();

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(202, $responseData['status']);
        $this->assertEquals('OK', $responseData['title']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('hasEpisodes', $responseData);
        $this->assertTrue($responseData['hasEpisodes']);
        $this->assertStringContainsString('5 episode(s)', $responseData['message']);
    }

    public function testHandleWithException()
    {
        $criteria = new Criteria('123', 1, 1, 'platform', 'universe');
        $ingestProcess = Mockery::mock(IngestProcessor::class);
        $ingestProcess->expects('ingest')
            ->once()
            ->with($criteria)
            ->andThrow(new \Exception('Test error message'));

        $ingest = new Ingest($criteria, $ingestProcess);
        $response = $ingest->handle();

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(500, $responseData['status']);
        $this->assertEquals('Error', $responseData['title']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Test error message', $responseData['message']);
        $this->assertEquals(500, $response->getStatusCode());
    }
}
