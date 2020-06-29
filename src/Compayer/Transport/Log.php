<?php

namespace Compayer\SDK\Transport;

/**
 * Class Log
 *
 * Collecting request and response params
 *
 * @package Compayer\SDK\Transport
 */
class Log
{
    const REQUEST_URI = 'REQUEST_URI';
    const REQUEST_METHOD = 'REQUEST_METHOD';
    const REQUEST_HEADERS = 'REQUEST_HEADERS';
    const REQUEST_BODY = 'REQUEST_BODY';

    const RESPONSE_BODY = 'RESPONSE_BODY';
    const RESPONSE_HEADERS = 'RESPONSE_HEADERS';
    const RESPONSE_STATUS_CODE = 'RESPONSE_STATUS_CODE';

    /** @var array Request params */
    private $request;

    /** @var array Response params */
    private $response;

    public function __construct()
    {
        $this->request = [];
        $this->response = [];
    }

    public function setRequestUri($uri) {
        return $this->addRequest(self::REQUEST_URI, $uri);
    }

    public function setRequestMethod($method) {
        return $this->addRequest(self::REQUEST_METHOD, $method);
    }

    public function setRequestHeaders($headers) {
        return $this->addRequest(self::REQUEST_HEADERS, $headers);
    }

    public function setRequestBody($body) {
        return $this->addRequest(self::REQUEST_BODY, $body);
    }

    public function setResponseStatusCode($statusCode) {
        return $this->addResponse(self::RESPONSE_STATUS_CODE, $statusCode);
    }

    public function setResponseHeaders($headers) {
        return $this->addResponse(self::RESPONSE_HEADERS, $headers);
    }

    public function setResponseBody($body) {
        return $this->addResponse(self::RESPONSE_BODY, $body);
    }

    public function addRequest($type, $value) {
        $this->request += [$type => $value];
        return $this;
    }

    public function addResponse($type, $value) {
        $this->response += [$type => $value];
        return $this;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Return Log as array
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
