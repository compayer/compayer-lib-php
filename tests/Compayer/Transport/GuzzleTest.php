<?php

namespace Compayer\SDK\Tests\Transport;

use Compayer\SDK\Exceptions\UnableToSendEvent;
use Compayer\SDK\Transport\Guzzle;
use PHPUnit\Framework\TestCase;

class GuzzleTest extends TestCase
{
    public function testFailingSend() {
        $this->expectException(UnableToSendEvent::class);
        (new Guzzle())->send('GET', 'url', [], '');
    }

    public function testSuccessSend() {
        (new Guzzle())->send('GET', 'https://www.google.com', [], '');
    }
}
