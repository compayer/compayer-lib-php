<?php

namespace Compayer\SDK\Tests;

use PHPUnit\Framework\TestCase;
use Compayer\SDK\Event;

class EventTest extends TestCase
{
    public function testFromArrayWithEmptyArray()
    {
        $event = Event::fromArray([]);
        $this->assertEquals('', $event->getTransactionId());
        $this->assertEquals('', $event->getMerchantTransactionId());
        $this->assertEquals(false, $event->isTest());
        $this->assertEquals('', $event->getDataSource());
        $this->assertEquals('', $event->getEvent());
        $this->assertEquals('', $event->getEventUrl());
        $this->assertEquals('', $event->getPaymentSystem());
        $this->assertEquals('', $event->getPaymentMethod());
        $this->assertEquals('', $event->getPaymentSubMethod());
        $this->assertEquals(0, $event->getPaymentAmount());
        $this->assertEquals(0, $event->getPaymentCost());
        $this->assertEquals('', $event->getPaymentCurrency());
        $this->assertEquals(0, $event->getVatAmount());
        $this->assertEquals('', $event->getVatCurrency());
        $this->assertEquals(0, $event->getFeesAmount());
        $this->assertEquals('', $event->getFeesCurrency());
        $this->assertEquals(0, $event->getPayoutAmount());
        $this->assertEquals('', $event->getPayoutCurrency());
        $this->assertEquals(1, $event->getPayoutExchangeRate());
        $this->assertEquals([], $event->getUserEmails());
        $this->assertEquals([], $event->getUserPhones());
        $this->assertEquals([], $event->getUserAccounts());
        $this->assertEquals('', $event->getUserIp());
        $this->assertEquals('', $event->getUserLang());
        $this->assertEquals('', $event->getOriginalRequest());
        $this->assertEquals([], $event->getExtra());
    }

    public function testFromArrayToArray()
    {
        $data = [
            'transactionId' => '3677eb06-1a9a-4b6c-9d6a-1799cae1b6bb',
            'merchantTransactionId' => '12345',
            'event' => '',
            'dataSource' => 'data_source',
            'dataChannel' => Event::DATA_CHANNEL,
            'isTest' => true,
            'eventUrl' => '',
            'paymentSystem' => 'paymentSystem',
            'paymentMethod' => 'paymentMethod',
            'paymentSubMethod' => 'paymentSubMethod',
            'paymentChannel' => '',
            'paymentAmount' => 1,
            'paymentCost' => 0.5,
            'paymentCurrency' => 'USD',
            'vatAmount' => 0.2,
            'vatCurrency' => 'EUR',
            'feesAmount' => 0.3,
            'feesCurrency' => 'GBR',
            'payoutAmount' => 0.5,
            'payoutCurrency' => 'GBR',
            'payoutExchangeRate' => 0.85,
            'userLang' => 'RUS',
            'userCountry' => '',
            'userEmails' => ['email'],
            'paymentDate' => '',
            'userPhones' => ['+74951111111'],
            'userAccounts' => ['23423465431'],
            'userIp' => '',
            'originalRequest' => '',
            'extra' => ['test' => 'test'],
        ];
        $event = Event::fromArray($data);

        $this->assertEquals($data, $event->toArray());
    }

    public function testFromArrayWithInvalidCompayerTransactionId()
    {
        $this->expectException(\InvalidArgumentException::class);
        Event::fromArray(['transactionId' => '12345']);
    }

    public function testFromArrayWithValidCompayerTransactionId()
    {
        Event::fromArray(['transactionId' => '3677eb06-1a9a-4b6c-9d6a-1799cae1b6bb']);
    }

    public function testFromArrayWithInvalidEvent()
    {
        $this->expectException(\InvalidArgumentException::class);
        Event::fromArray(['event' => 'test']);
    }

    public function testFromArrayWithValidEvent()
    {
        Event::fromArray(['event' => Event::EVENT_START]);
    }

    public function testEmptyGetPaymentSystemResponse()
    {
        $event = Event::fromArray([]);
        $this->assertEquals('', $event->getPaymentSystemResponse());
    }

    public function testNotEmptyGetPaymentSystemResponse()
    {
        $event = Event::fromArray(['paymentSystemResponse' => 'response']);
        $this->assertEquals('response', $event->getPaymentSystemResponse());
    }

    public function testSetExtraWithExistsPaymentSystemResponse()
    {
        $event = Event::fromArray(['paymentSystemResponse' => 'response']);
        $event->setExtra(['php' => 'unit']);
        $this->assertEquals('response', $event->getPaymentSystemResponse());
        $this->assertEquals(['php' => 'unit', Event::EXTRA_RESPONSE => 'response'], $event->getExtra());
    }

    public function testResolveIpFromHttpClientIp()
    {
        $_SERVER['HTTP_CLIENT_IP'] = 'HTTP_CLIENT_IP';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $_SERVER['REMOTE_ADDR'] = '';
        $event = Event::fromArray([]);
        $this->assertEquals($_SERVER['HTTP_CLIENT_IP'], $event->getUserIp());
    }

    public function testResolveIpFromHttpXForwardedFor()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'HTTP_X_FORWARDED_FOR';
        $_SERVER['REMOTE_ADDR'] = '';
        $event = Event::fromArray([]);
        $this->assertEquals($_SERVER['HTTP_X_FORWARDED_FOR'], $event->getUserIp());
    }

    public function testResolveIpFromRemoteAddr()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $_SERVER['REMOTE_ADDR'] = 'REMOTE_ADDR';
        $event = Event::fromArray([]);
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $event->getUserIp());
    }

    public function testResolveEventUrl()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['SERVER_PORT'] = '8080';
        $_SERVER['SERVER_NAME'] = 'compayer.com';
        $_SERVER['REQUEST_URI'] = '/test?a=1&b=2';
        $url = 'http://compayer.com:8080' . $_SERVER['REQUEST_URI'];
        $event = Event::fromArray([]);
        $this->assertEquals($url, $event->getEventUrl());
    }
}
