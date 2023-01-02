<?php

namespace JOOservices\XClient\Tests\Unit;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JOOservices\XClient\Factory;
use JOOservices\XClient\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class FactoryTest extends TestCase
{
    /**
     * @var mixed|MockObject|LoggerInterface
     */
    private mixed $logger;
    private Factory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->factory = new Factory(200);
    }

    public function test_request_error()
    {
        $factory = new Factory($this->faker->numberBetween(400, 599));
        $client = $factory->make();
        $this->expectException(RequestException::class);
        $client->request('GET', $this->faker->url);
    }

    /**
     * @dataProvider data_provider_logging_level
     * @throws GuzzleException
     */
    public function test_with_logging(string $level)
    {
        $url = $this->faker->url;
        $client = $this->factory->enableLogging($this->logger, 'log request: {method} {uri}', $level)->make();
        $this->logger->expects($this->once())
            ->method('log')
            ->with(constant('Psr\\Log\\LogLevel::' . strtoupper($level)), 'log request: GET ' . $url);
        $client->request('GET', $url);
    }

    public function data_provider_logging_level()
    {
        return [
            [LogLevel::INFO,],
            [LogLevel::ALERT,],
            [LogLevel::CRITICAL,],
            [LogLevel::ERROR,],
            [LogLevel::WARNING,],
            [LogLevel::NOTICE,],
            [LogLevel::INFO,],
            [LogLevel::DEBUG,],
        ];
    }

    public function test_fake_response_code()
    {
        $client = $this->factory->addOptions(['base_uri', $this->faker->url])->make();
        $this->assertEquals(200, $client->request('GET', 'path')->getStatusCode());
    }

    public function test_get_history()
    {
        $url = $this->faker->url;
        $client = $this->factory->make();
        $client->request('GET', $url);

        $history = $this->factory->getHistory($client);

        $this->assertNotEmpty($history[0]);
        $request = $history[0]['request'];
        $response = $history[0]['response'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals($url, $request->getUri());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Fake test response for request: GET ' . $url, $response->getBody());
    }

    public function test_retries()
    {
        $client = $this->factory->enableRetries(2, 0.001, 200)->make();
        $client->request('get', $this->faker->url);
        $this->assertEquals(3, count($this->factory->getHistory($client)));
    }

    public function test_retries_with_higher_min_error_code()
    {
        $factory = new Factory(202);
        $client = $factory->enableRetries(2, 0.001, 200)->make();
        $client->request('GET', $this->faker->url);
        $this->assertEquals(0, count($this->factory->getHistory($client)));
    }

    public function test_get_status_code()
    {
        $factory = new Factory( 202);
        $client = $factory->enableRetries(2, 0.001, 200)->make();
        $response = $client->request('GET', $this->faker->url);
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertEquals(0, count($this->factory->getHistory($client)));
    }
}