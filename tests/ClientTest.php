<?php

namespace Compayer\SDK\Tests;

use Compayer\SDK\Exceptions\UnableToFindUserIdentity;
use Compayer\SDK\Exceptions\UnableToSendEvent;
use PHPUnit\Framework\TestCase;
use Compayer\SDK\Client;
use Compayer\SDK\Event;
use Mockery;

class ClientTest extends TestCase
{
    public function testCreateWithoutRequiredParams()
    {
        $this->expectException(\InvalidArgumentException::class);
        Client::create([]);
    }

    public function testCreateWithRequiredParams()
    {
        $this->assertInstanceOf("\Compayer\SDK\Client", $this->getClient());
    }

    public function testUnableToSendEvent()
    {
        $this->expectException(UnableToSendEvent::class);
        $client = $this->getClient();

        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andThrow(new UnableToSendEvent);
        $client->setTransport($transport);

        $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
    }

    public function testUnableToFindUserIdentity()
    {
        $this->expectException(UnableToFindUserIdentity::class);
        $client = $this->getClient();
        $client->pushStartEvent(Event::fromArray([]));
    }

    public function testPushStartEvent()
    {
        $client = $this->getClient();

        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn([true]);
        $client->setTransport($transport);

        $transactionId = $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
        $this->assertRegExp("/([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})/", $transactionId);
    }

    public function testPushSuccessEventWithoutExtraResponse()
    {
        $this->expectException(\InvalidArgumentException::class);
        $client = $this->getClient();
        $client->pushSuccessEvent(Event::fromArray([]));
    }

    public function testPushSuccessEventWithResponse()
    {
        $client = $this->getClient();
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn([true]);
        $client->setTransport($transport);

        $result = $client->pushSuccessEvent(Event::fromArray([
            'userAccounts' => ['123'],
            'extra' => [Event::EXTRA_RESPONSE => "response"],
        ]));
        $this->assertTrue($result);
    }

    public function testPushFailEventWithoutResponse()
    {
        $this->expectException(\InvalidArgumentException::class);
        $client = $this->getClient();
        $client->pushFailEvent(Event::fromArray([]));
    }

    public function testPushFailEventWithResponse()
    {
        $client = $this->getClient();
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn([true]);
        $client->setTransport($transport);

        $result = $client->pushFailEvent(Event::fromArray([
            'userAccounts' => ['123'],
            'extra' => [Event::EXTRA_RESPONSE => "response"],
        ]));
        $this->assertTrue($result);
    }

    private function getClient()
    {
        return Client::create([Client::CONFIG_DATA_SOURCE => "data_source", Client::CONFIG_SECRET_KEY => "secret_key"]);
    }
}
