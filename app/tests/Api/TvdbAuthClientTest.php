<?php

namespace App\Tests\Api;

use App\Api\TvdbAuthClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TvdbAuthClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TvdbAuthClient $unit;
    private HttpClientInterface $client;

    public function setUp(): void
    {
        $this->client = Mockery::mock(HttpClientInterface::class);
        $this->unit = new TvdbAuthClient(
            $this->client,
            'apikey',
            'pin'
        );
    }

    public function testLogin(): void
    {

        $this->client->expects('request')
            ->with(
                'POST',
                'https://api4.thetvdb.com/v4/login',
                [
                    'json' => [
                        'apikey' => 'apikey',
                        'pin' => 'pin'
                    ]
                ]
            )
            ->andReturns(new MockResponse('{"token": "token"}'));

        $this->unit->login();
    }

    public function testLoginThrowsExceptionOnTransportException(): void
    {
        $this->client->expects('request')
            ->andThrows($this->createMock(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while logging in');
        $this->unit->login();
    }
}
