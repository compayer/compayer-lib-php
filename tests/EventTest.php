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
        $this->assertEquals('', $event->getUserIp());
        $this->assertEquals('', $event->getOriginalRequest());
        $this->assertEquals([], $event->getExtra());
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
}
