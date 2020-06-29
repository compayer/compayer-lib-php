<?php

namespace Compayer\SDK\Tests;

use Compayer\SDK\Transport\Log;
use Compayer\SDK\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Compayer\SDK\Config;

class ConfigTest extends TestCase
{
    public function testWithDefaultParams()
    {
        $config = new Config('clientId', 'secretKey');
        $this->assertEquals('clientId', $config->getClientId());
        $this->assertEquals('secretKey', $config->getSecretKey());
        $this->assertEquals('https://compayer.pay.super.com', $config->getEventApiUrl());
        $this->assertEquals(false, $config->isSandboxMode());
        $this->assertEquals(false, $config->isDebugMode());
        $this->assertInstanceOf('Compayer\SDK\Transport\Guzzle', $config->getTransport());
    }

    public function testWithCustomParams()
    {
        $config = (new Config('clientId', 'secretKey'))
            ->setClientId('clientId2')
            ->setSecretKey('secretKey2')
            ->setEventApiUrl('url')
            ->setSandboxMode(true)
            ->setDebugMode(true)
            ->setTransport(new CustomTransport());

        $this->assertEquals('clientId2', $config->getClientId());
        $this->assertEquals('secretKey2', $config->getSecretKey());
        $this->assertEquals('url', $config->getEventApiUrl());
        $this->assertEquals(true, $config->isSandboxMode());
        $this->assertEquals(true, $config->isDebugMode());
        $this->assertInstanceOf('Compayer\SDK\Tests\CustomTransport', $config->getTransport());
    }
}

class CustomTransport implements TransportInterface {
    public function send($method, $url, $headers, $body) {
        return new Log(200, [], 'body');
    }
}
