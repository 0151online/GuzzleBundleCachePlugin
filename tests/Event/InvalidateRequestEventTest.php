<?php

namespace Gregurco\Bundle\GuzzleBundleCachePlugin\Test\Event;

use Gregurco\Bundle\GuzzleBundleCachePlugin\Event\InvalidateRequestEvent;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Prophecy\Prophecy\ObjectProphecy;

class InvalidateRequestEventTest extends TestCase
{
    public function testGeneralUseCase()
    {
        $baseUri = Psr7\uri_for('http://api.domain.tld');

        /** @var Client|ObjectProphecy $client */
        $client = $this->prophesize(Client::class);

        $client->getConfig('base_uri')->willReturn($baseUri);
        $client->getConfig('base_uri')->shouldBeCalledOnce();

        $invalidateRequestEvent = new InvalidateRequestEvent($client->reveal(), 'GET', '/ping');
        $this->assertEquals($client->reveal(), $invalidateRequestEvent->getClient());
        $this->assertEquals('GET', $invalidateRequestEvent->getMethod());
        $this->assertEquals('/ping', $invalidateRequestEvent->getUri());

        $request = $invalidateRequestEvent->getRequest();
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('http://api.domain.tld/ping', (string)$request->getUri());
    }

    public function testCaseWhenClientWithoutBaseUri()
    {
        /** @var Client|ObjectProphecy $client */
        $client = $this->prophesize(Client::class);

        $client->getConfig('base_uri')->willReturn(null);
        $client->getConfig('base_uri')->shouldBeCalledOnce();

        $invalidateRequestEvent = new InvalidateRequestEvent($client->reveal(), 'GET', 'http://api.domain.tld/ping');
        $this->assertEquals($client->reveal(), $invalidateRequestEvent->getClient());
        $this->assertEquals('GET', $invalidateRequestEvent->getMethod());
        $this->assertEquals('http://api.domain.tld/ping', $invalidateRequestEvent->getUri());

        $request = $invalidateRequestEvent->getRequest();
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('http://api.domain.tld/ping', (string)$request->getUri());
    }
}
