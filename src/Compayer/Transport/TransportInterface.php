<?php

namespace Compayer\SDK\Transport;

interface TransportInterface
{
    public function send($method, $url, $headers, $data);
}
