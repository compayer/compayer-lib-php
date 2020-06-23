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

    public function testCreateWithAllParams()
    {
        $client = Client::create([
            Client::CONFIG_DATA_SOURCE => "data_source",
            Client::CONFIG_SECRET_KEY => "secret_key",
            Client::CONFIG_SANDBOX_MODE => true,
            Client::CONFIG_EVENT_API_URL => 'http://compayer.com',
        ]);
        $this->assertInstanceOf("\Compayer\SDK\Client", $client);
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

    public function testPushRefundEventWithoutResponse()
    {
        $this->expectException(\InvalidArgumentException::class);
        $client = $this->getClient();
        $client->pushRefundEvent(Event::fromArray([]));
    }

    public function testRefundFailEventWithResponse()
    {
        $client = $this->getClient();
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn([true]);
        $client->setTransport($transport);

        $result = $client->pushRefundEvent(Event::fromArray([
            'userAccounts' => ['123'],
            'extra' => [Event::EXTRA_RESPONSE => "response"],
        ]));
        $this->assertTrue($result);
    }

    public function testSetTestEventForSandboxMode()
    {
        $client = Client::create([
            Client::CONFIG_DATA_SOURCE => "data_source",
            Client::CONFIG_SECRET_KEY => "secret_key",
            Client::CONFIG_SANDBOX_MODE => true,
        ]);
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')
            ->with('POST', 'https://compayer.pay.super.com/push/v2/data_source', Mockery::any(), Mockery::on(function ($argument) {
                return (bool)preg_match('/"isTest":true/', $argument);
            }))
            ->andReturn([true]);
        $client->setTransport($transport);
        $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
    }

    public function testSetEventApiUrl()
    {
        $client = Client::create([
            Client::CONFIG_DATA_SOURCE => "data_source",
            Client::CONFIG_SECRET_KEY => "secret_key",
            Client::CONFIG_EVENT_API_URL => 'http://compayer.com',
        ]);
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')
            ->with('POST', 'http://compayer.com/push/v2/data_source', Mockery::any(), Mockery::any())
            ->andReturn([true]);
        $client->setTransport($transport);
        $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
    }

    private function getClient()
    {
        return Client::create([Client::CONFIG_DATA_SOURCE => "data_source", Client::CONFIG_SECRET_KEY => "secret_key"]);
    }
}
