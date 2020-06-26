<?php

namespace Compayer\SDK;

/**
 * Class Response
 *
 * Representation of response send of Event
 *
 * @package Compayer\SDK
 */
class Response
{
    /** @var string Event transaction identifier */
    private $transactionId;

    /** @var array Debug log */
    private $log;

    public function __construct($transactionId, $log = []) {
        $this->transactionId = $transactionId;
        $this->log = $log;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     * @return Response
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param array $log
     * @return Response
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }
}
