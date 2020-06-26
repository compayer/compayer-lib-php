<?php

namespace Compayer\SDK;

use Compayer\SDK\Exceptions\UnableToFindPaymentSystemResponse;
use Compayer\SDK\Exceptions\UnableToFindUserIdentity;
use Compayer\SDK\Exceptions\UnableToSendEvent;
use Compayer\SDK\Transport\Guzzle;
use Compayer\SDK\Transport\TransportInterface;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Class Client
 *
 * Client validate and send the Event to API
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

    /** @var Config Configuration options */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Send start Event
     * @param Event $event
     * @return Response Event transaction identifier
     * @throws InvalidArgumentException
     * @throws UnableToSendEvent
     * @throws UnableToFindUserIdentity
     */
    public function pushStartEvent(Event $event)
    {
        $event->setTransactionId(Uuid::uuid4()->toString());
        $event->setEvent(Event::EVENT_START);

        return $this->pushEvent($event);
    }

    /**
     * Send success Event
     * @param Event $event
     * @return Response
     * @throws UnableToFindPaymentSystemResponse
     * @throws UnableToFindUserIdentity
     * @throws UnableToSendEvent
     */
    public function pushSuccessEvent(Event $event)
    {
        $event->setEvent(Event::EVENT_SUCCESS);
        return $this->pushEvent($event);
    }

    /**
     * Send fail Event
     * @param Event $event
     * @return Response
     * @throws UnableToFindPaymentSystemResponse
     * @throws UnableToFindUserIdentity
     * @throws UnableToSendEvent
     */
    public function pushFailEvent(Event $event)
    {
        $event->setEvent(Event::EVENT_FAIL);
        return $this->pushEvent($event);
    }

    /**
     * Send refund Event
     * @param Event $event
     * @return Response
     * @throws UnableToFindPaymentSystemResponse
     * @throws UnableToFindUserIdentity
     * @throws UnableToSendEvent
     */
    public function pushRefundEvent(Event $event)
    {
        $event->setEvent(Event::EVENT_REFUND);
        return $this->pushEvent($event);
    }

    /**
     * @param Event $event
     * @return Response
     * @throws UnableToFindPaymentSystemResponse
     * @throws UnableToFindUserIdentity
     * @throws UnableToSendEvent
     */
    private function pushEvent(Event $event)
    {
        $this->checkPaymentSystemResponse($event);
        $this->checkEventUserIdentity($event);

        $event->setDataSource($this->config->getClientId());
        $event->setIsTest($this->config->isSandboxMode());
        $data = json_encode($event->toArray());

        $token = hash(self::TOKEN_HASH_ALG, $data . $this->config->getSecretKey());
        $url = sprintf('%s/push/v%d/%s', $this->config->getEventApiUrl(), self::VERSION, $this->config->getClientId());

        $headers = [
            'Authorization' => "Bearer {$token}",
            'Content-Length' => mb_strlen($data),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->getVersion(),
        ];

        $log = [];
        $executionStartTime = microtime(true);
        $method = 'POST';

        $response = $this->config->getTransport()->send($method, $url, $headers, $data);

        if ($this->config->isDebugMode()) {
            $log = [
                'event' => $event,
                'headers' => $headers,
                'url' => $url,
                'method' => $method,
                'response' => $response->toArray(),
                'execution_time' => microtime(true) - $executionStartTime];
        }

        return new Response($event->getTransactionId(), $log);
    }

    private function checkPaymentSystemResponse(Event $event)
    {
        if ($event->getEvent() === Event::EVENT_START) {
            return;
        }

        $extra = $event->getExtra();
        if (!$extra || !array_key_exists(Event::EXTRA_RESPONSE, $extra)) {
            throw new UnableToFindPaymentSystemResponse('Payment system response message cannot be empty');
        }
    }

    private function checkEventUserIdentity(Event $event)
    {
        if (!$event->getUserEmails() && !$event->getUserPhones() && !$event->getUserAccounts()) {
            throw new UnableToFindUserIdentity('User identity not found');
        }
    }

    private function getVersion()
    {
        return sprintf('compayer-sdk-php/%d PHP/%s', self::VERSION, PHP_VERSION);
    }
}
