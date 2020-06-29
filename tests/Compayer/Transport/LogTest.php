<?php

namespace Compayer\SDK\Tests\Transport;

use Compayer\SDK\Transport\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testDefaultRequestResponse()
    {
        $response = new Log();
        $this->assertEquals([], $response->getRequest());
        $this->assertEquals([], $response->getResponse());
    }

    public function testSpecialSetters()
    {
        $response = (new Log())
            ->setRequestUri('uri')
            ->setRequestMethod('method')
            ->setRequestHeaders(['request' => 'request'])
            ->setRequestBody('request_body')
            ->setResponseStatusCode(200)
            ->setResponseHeaders(['response' => 'response'])
            ->setResponseBody('response_body');

        $this->assertEquals([
            Log::REQUEST_URI => 'uri',
            Log::REQUEST_METHOD => 'method',
            Log::REQUEST_HEADERS => ['request' => 'request'],
            Log::REQUEST_BODY => 'request_body',
        ], $response->getRequest());

        $this->assertEquals([
            Log::RESPONSE_STATUS_CODE => 200,
            Log::RESPONSE_HEADERS => ['response' => 'response'],
            Log::RESPONSE_BODY => 'response_body',
        ], $response->getResponse());
    }

    public function testCommonSetters()
    {
        $response = (new Log())
            ->addRequest('request_key', 'request_value')
            ->addResponse('response_key', 'response_value');

        $this->assertEquals(['request_key' => 'request_value'], $response->getRequest());
        $this->assertEquals(['response_key' => 'response_value'], $response->getResponse());
    }

    public function testToArray()
    {
        $response = new Log();
        $expected = [
            'request' => [],
            'response' => [],
        ];
        $this->assertEquals($expected, $response->toArray());
    }
}
