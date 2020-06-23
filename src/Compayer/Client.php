<?php

namespace Compayer\SDK;

use Compayer\SDK\Exceptions\UnableToFindUserIdentity;
use Compayer\SDK\Exceptions\UnableToSendEvent;
use Compayer\SDK\Transport\Guzzle;
use Compayer\SDK\Transport\TransportInterface;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Class Client
 *
 * @package Compayer\SDK
 * @author Vadim Sabirov (vadim.sabirov@protocol.one)
 *
 */
class Client
{
    /** Version of SDK */
    const VERSION = 2;

    /** Hash algorithm for creating bearer token */
    const TOKEN_HASH_ALG = 'sha256';

    /** Initialize option of data source ID for SDK */
    const CONFIG_DATA_SOURCE = 'data_source';

    /** Initialize option of API secret key for SDK */
    const CONFIG_SECRET_KEY = 'secret_key';

    /** Initialize option of event API url for SDK */
    const CONFIG_EVENT_API_URL = 'event_api_url';

    /** Initialize option of sandbox mode for SDK */
    const CONFIG_SANDBOX_MODE = 'sandbox_mode';

    /** @var string Data source identifier */
    private $dataSourceId;

    /** @var string Secret key for API */
    private $secretKey;

    /** @var string URL of endpoint to save the Event log */
    private $eventApiUrl;

    /** @var bool Test mode */
    private $sandboxMode = false;

    /** @var TransportInterface */
    private $transport;

    private function __construct()
    {
        $this->eventApiUrl = sprintf('https://compayer.pay.super.com/push/v%d', self::VERSION);
        $this->transport = new Guzzle();
    }

    /**
     * Create SDK client with config
     * @param array $config Configuration params (use the CONFIG_* constants for array keys)
     * @return self
     */
    public static function create(array $config)
    {
        $required = [self::CONFIG_DATA_SOURCE, self::CONFIG_SECRET_KEY];

        if ($missing = array_diff($required, array_keys($config))) {
            throw new \InvalidArgumentException('Missing the required params: ' . implode(', ', $missing));
        }

        $client = new self();
        $client->dataSourceId = $config[self::CONFIG_DATA_SOURCE];
        $client->secretKey = $config[self::CONFIG_SECRET_KEY];

        if (isset($config[self::CONFIG_EVENT_API_URL])) {
            $client->eventApiUrl = $config[self::CONFIG_EVENT_API_URL];
        }

        if (isset($config[self::CONFIG_SANDBOX_MODE])) {
            $client->sandboxMode = $config[self::CONFIG_SANDBOX_MODE];
        }

        return $client;
    }

    /**
     * Set http transport for requests
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport) {
        $this->transport = $transport;
    }

    /**
     * Send start Event
     * @param Event $event
     * @return string Event transaction identifier
     * @throws InvalidArgumentException
     * @throws UnableToSendEvent
     * @throws UnableToFindUserIdentity
     */
    public function pushStartEvent(Event $event)
    {
        $event->setTransactionId(Uuid::uuid4()->toString());
        $event->setEvent(Event::EVENT_START);
        $this->pushEvent($event);

        return $event->getTransactionId();
    }

    /**
     * Send success Event
     * @param Event $event
     * @return bool
     * @throws UnableToSendEvent
     */
    public function pushSuccessEvent(Event $event)
    {
        $extra = $event->getExtra();
        if (!$extra || !array_key_exists(Event::EXTRA_RESPONSE, $extra)) {
            throw new InvalidArgumentException('Payment system response message cannot be empty');
        }

        $event->setEvent(Event::EVENT_SUCCESS);
        return $this->pushEvent($event);
    }

    /**
     * Send fail Event
     * @param Event $event
     * @return bool
     * @throws InvalidArgumentException
     * @throws UnableToSendEvent
     */
    public function pushFailEvent(Event $event)
    {
        $extra = $event->getExtra();
        if (!$extra || !array_key_exists(Event::EXTRA_RESPONSE, $extra)) {
            throw new InvalidArgumentException('Payment system response message cannot be empty');
        }

        $event->setEvent(Event::EVENT_FAIL);
        return $this->pushEvent($event);
    }

    /**
     * @param Event $event
     * @return bool
     * @throws UnableToSendEvent
     */
    private function pushEvent(Event $event)
    {
        $this->checkEventUserIdentity($event);

        $event->setIsTest($this->sandboxMode);
        $data = json_encode($event->toArray());

        $token = hash(self::TOKEN_HASH_ALG, $data . $this->secretKey);
        $url = sprintf('%s/%s', $this->eventApiUrl, $this->dataSourceId);

        $headers = [
            'Authorization' => "Bearer {$token}",
            'Content-Length' => mb_strlen($data),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->getVersion(),
        ];

        $this->transport->send('POST', $url, $headers, $data);

        return true;
    }

    private function checkEventUserIdentity(Event $event) {
        if (!$event->getUserEmails() && !$event->getUserPhones() && !$event->getUserAccounts()) {
            throw new UnableToFindUserIdentity('User identity not found');
        }
    }

    private function getVersion()
    {
        return sprintf('compayer-sdk-php/%d PHP/%s', self::VERSION, PHP_VERSION);
    }
}
