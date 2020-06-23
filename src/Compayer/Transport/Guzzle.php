<?php

namespace Compayer\SDK\Transport;

use Compayer\SDK\Exceptions\UnableToSendEvent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class Guzzle implements TransportInterface
{
    public function send($method, $url, $headers, $data)
    {
        $client = new Client([
            'verify' => false,
        ]);
        $request = new Request($method, $url, $headers, $data);

        try {
            $client->send($request);
        } catch (GuzzleException $e) {
            throw new UnableToSendEvent('Unable to send Event', 500, $e);
        }
    }
}
