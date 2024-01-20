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
            ->with($criteria);

        $ingest = new Ingest($criteria, $ingestProcess);
        $response = $ingest->handle();

        $this->assertEquals(
            [
                'message' => 'Processing started for series: 123 from Season: 1, Episode:1',
                'status' => 202,
                'title' => 'OK'
            ],
            json_decode($response->getContent(), true)
        );
    }
}
