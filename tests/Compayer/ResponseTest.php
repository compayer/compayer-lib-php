<?php

namespace Compayer\SDK\Tests;

use Compayer\SDK\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testWithConstructorParams() {
        $response = new Response('id', []);
        $this->assertEquals('id', $response->getTransactionId());
        $this->assertEquals([], $response->getLog());
    }

    public function testSetters() {
        $response = (new Response('id', []))
            ->setTransactionId('id2')
            ->setLog(['key' => 'value']);

        $this->assertEquals('id2', $response->getTransactionId());
        $this->assertEquals(['key' => 'value'], $response->getLog());
    }
}
