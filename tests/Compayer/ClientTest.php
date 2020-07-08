<?php

namespace Compayer\SDK\Tests;

use Compayer\SDK\Config;
use Compayer\SDK\Exceptions\UnableToFindPaymentSystemResponse;
use Compayer\SDK\Exceptions\UnableToFindUserIdentity;
use Compayer\SDK\Exceptions\UnableToSendEvent;
use Compayer\SDK\Transport\Log;
use PHPUnit\Framework\TestCase;
use Compayer\SDK\Client;
use Compayer\SDK\Event;
use Mockery;

class ClientTest extends TestCase
{
    public function testUnableToSendEvent()
    {
        $this->expectException(UnableToSendEvent::class);

        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andThrow(new UnableToSendEvent);

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport);
        $client = new Client($config);

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
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn(new Log());

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport);
        $client = new Client($config);

        $response = $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
        $this->assertInstanceOf('Compayer\SDK\Response', $response);
        $this->assertRegExp("/([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})/", $response->getTransactionId());
    }

    public function testPushSuccessEventWithoutExtraResponse()
    {
        $this->expectException(UnableToFindPaymentSystemResponse::class);

        $client = $this->getClient();
        $client->pushSuccessEvent(Event::fromArray([]));
    }

    public function testPushSuccessEventWithResponse()
    {
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn(new Log());

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport);
        $client = new Client($config);

        $response = $client->pushSuccessEvent(Event::fromArray([
            'userAccounts' => ['123'],
            'extra' => [Event::EXTRA_RESPONSE => "response"],
        ]));
        $this->assertInstanceOf('Compayer\SDK\Response', $response);
    }

    public function testPushFailEventWithoutResponse()
    {
        $this->expectException(UnableToFindPaymentSystemResponse::class);

        $client = $this->getClient();
        $client->pushFailEvent(Event::fromArray([]));
    }

    public function testPushFailEventWithResponse()
    {
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn(new Log());

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport);
        $client = new Client($config);

        $response = $client->pushFailEvent(Event::fromArray([
            'userAccounts' => ['123'],
            'extra' => [Event::EXTRA_RESPONSE => "response"],
        ]));
        $this->assertInstanceOf('Compayer\SDK\Response', $response);
    }

    public function testPushRefundEventWithoutResponse()
    {
        $this->expectException(UnableToFindPaymentSystemResponse::class);

        $client = $this->getClient();
        $client->pushRefundEvent(Event::fromArray([]));
    }

    public function testRefundFailEventWithResponse()
    {
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn(new Log());

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport);
        $client = new Client($config);

        $response = $client->pushRefundEvent(Event::fromArray([
            'userAccounts' => ['123'],
            'extra' => [Event::EXTRA_RESPONSE => "response"],
        ]));
        $this->assertInstanceOf('Compayer\SDK\Response', $response);
    }

    public function testSetTestEventForSandboxMode()
    {
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')
            ->with('POST', 'https://receiver.compayer.com/push/v2/clientId', Mockery::any(), Mockery::on(function ($argument) {
                return (bool)preg_match('/"isTest":true/', $argument);
            }))
            ->andReturn(new Log());

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport)->setSandboxMode(true);
        $client = new Client($config);

        $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
    }

    public function testSetEventApiUrl()
    {
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')
            ->with('POST', 'http://compayer.com/push/v2/clientId', Mockery::any(), Mockery::any())
            ->andReturn(new Log());

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport)->setEventApiUrl('http://compayer.com');
        $client = new Client($config);

        $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
    }

    public function testSetDebugMode()
    {
        $transport = Mockery::mock('Compayer\SDK\Transport\TransportInterface');
        $transport->shouldReceive('send')->andReturn(new Log());

        $config = new Config('clientId', 'secretKey');
        $config->setTransport($transport)->setDebugMode(true);
        $client = new Client($config);

        $response = $client->pushStartEvent(Event::fromArray(['userAccounts' => ['123']]));
        $this->assertNotEmpty($response->getLog());
    }

    private function getClient()
    {
        return new Client(new Config('clientId', 'secretKey'));
    }
}
