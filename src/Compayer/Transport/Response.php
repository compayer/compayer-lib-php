<?php

namespace Compayer\SDK\Transport;

class Response
{
    /** @var int HTTP status code */
    private $status;

    /** @var array Headers of response */
    private $headers;

    /** @var string body */
    private $body;

    /**
     * Response constructor
     * @param int $status
     * @param array $headers
     * @param string $body
     */
    public function __construct($status = 200, $headers = [], $body = '')
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Response
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Response
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Return Event as array
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
