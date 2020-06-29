<?php

namespace Compayer\SDK\Transport;

interface TransportInterface
{
    /**
     * @param string $method Method of request
     * @param string $url Requesting URL
     * @param array $headers Headers params
     * @param string $body Body message
     * @return Log
     */
    public function send($method, $url, $headers, $body);
}
