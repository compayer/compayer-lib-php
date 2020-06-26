<?php

namespace Compayer\SDK\Tests\Transport;

use Compayer\SDK\Transport\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testWithConstructorParams() {
        $response = new Response(0, [], '');
        $this->assertEquals(0, $response->getStatus());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals('', $response->getBody());
    }

    public function testSetters() {
        $response = (new Response(0, [], ''))
            ->setStatus(1)
            ->setHeaders(['key' => 'value'])
            ->setBody('body');

        $this->assertEquals(1, $response->getStatus());
        $this->assertEquals(['key' => 'value'], $response->getHeaders());
        $this->assertEquals('body', $response->getBody());
    }

    public function testToArray() {
        $response = new Response(200, ['key' => 'value'], 'body');
        $expected = [
            'status' => 200,
            'headers' => ['key' => 'value'],
            'body' => 'body',
        ];
        $this->assertEquals($expected, $response->toArray());
    }
}
