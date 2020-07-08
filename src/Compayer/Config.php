<?php

namespace Compayer\SDK;

use Compayer\SDK\Transport\Guzzle;
use Compayer\SDK\Transport\TransportInterface;

/**
 * Class Config
 *
 * Configuration class for Client
 *
 * @package Compayer\SDK
 */
class Config
{
    /** @var string Client identifier */
    private $clientId;

    /** @var string Secret key for API */
    private $secretKey;

    /** @var string URL of endpoint to save the Event log */
    private $eventApiUrl;

    /** @var TransportInterface */
    private $transport;

    /** @var bool Test mode */
    private $sandboxMode;

    /** @var bool Debug mode */
    private $debugMode;

    public function __construct($clientId, $secretKey)
    {
        $this->clientId = $clientId;
        $this->secretKey = $secretKey;
        $this->eventApiUrl = 'https://data.compayer.com';
        $this->transport = new Guzzle();
        $this->sandboxMode = false;
        $this->debugMode = false;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return Config
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     * @return Config
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventApiUrl()
    {
        return $this->eventApiUrl;
    }

    /**
     * @param string $eventApiUrl
     * @return Config
     */
    public function setEventApiUrl($eventApiUrl)
    {
        $this->eventApiUrl = $eventApiUrl;
        return $this;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param TransportInterface $transport
     * @return Config
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSandboxMode()
    {
        return $this->sandboxMode;
    }

    /**
     * @param bool $sandboxMode
     * @return Config
     */
    public function setSandboxMode($sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param bool $debugMode
     * @return Config
     */
    public function setDebugMode($debugMode)
    {
        $this->debugMode = $debugMode;
        return $this;
    }
}
