<?php

namespace Compayer\SDK;

use DateTime;
use InvalidArgumentException;

/**
 * Class Event
 *
 * Contains all properties of Event
 *
 * @package Compayer\SDK
 * @author Vadim Sabirov (vadim.sabirov@protocol.one)
 */
class Event
{
    /** Event type start */
    const EVENT_START = 'start';

    /** Event type success */
    const EVENT_SUCCESS = 'success';

    /** Event type fail */
    const EVENT_FAIL = 'fail';

    /** Type of SDK data channel */
    const DATA_CHANNEL = 'back';

    /** Extra field for response from payment system of success or fail message */
    const EXTRA_RESPONSE = 'response';

    /** Name of Yandex Money payment system */
    const PAYMENT_SYSTEM_YANDEX = 'Yandex.Money';

    /** Name of Xsolla payment system */
    const PAYMENT_SYSTEM_XSOLLA = 'Xsolla';

    /** Name of Paysuper payment system */
    const PAYMENT_SYSTEM_PAYSUPER = 'Paysuper';

    /** Name of bank cards payment method */
    const PAYMENT_METHOD_BANK_CARD = 'Bank cards';

    /** Name of e-payments method */
    const PAYMENT_METHOD_E_PAYMENTS = 'E-payments';

    /** Name of bank transfer payment method */
    const PAYMENT_METHOD_BANK_TRANSFER = 'Bank Transfer';

    /** Name of mobile payment method */
    const PAYMENT_METHOD_MOBILE_PAYMENTS = 'Mobile Payments';

    /** Name of cash payment method */
    const PAYMENT_METHOD_CASH_PAYMENTS = 'Cash Payments';

    /** Name of prepaid cars payment method */
    const PAYMENT_METHOD_PREPAID_CARDS = 'Prepaid Cards';

    /** Name of crypto currency payment method */
    const PAYMENT_METHOD_CRYPTO_CURRENCY = 'Cryptocurrency';

    /** @var string Compayer transaction identifier (auto generated for the start Event) */
    private $transactionId;

    /** @var string Merchant transaction identifier */
    private $merchantTransactionId;

    /** @var string Event type (Automatically populated when sending the specified type of Event) */
    private $event;

    /** @var string Data source identifier */
    private $dataSource;

    /** @var string Data source channel type (always "back" for SDK type) */
    private $dataChannel;

    /** @var bool The Event is a test */
    private $isTest;

    /** @var string URL of page where the event was triggered (Automatically populated) */
    private $eventUrl;

    /** @var string Payment system name (use the PAYMENT_SYSTEM_* constants for main payment systems or any custom name) */
    private $paymentSystem;

    /** @var string Payment method name (use the PAYMENT_METHOD_* constants for main payment method or any custom name) */
    private $paymentMethod;

    /** @var string Payment sub method name (any custom name e.g. Webmoney Purse, QIWI, Unknown Bank Card & etc.) */
    private $paymentSubMethod;

    /** @deprecated */
    private $paymentChannel;

    /** @var double Invoice amount for user payment */
    private $paymentAmount;

    /** @var double The amount of the VAT amount + fee amount */
    private $paymentCost;

    /** @var string Currency of payment (based on the ISO 4217 currency code) */
    private $paymentCurrency;

    /** @var double The amount of the VAT */
    private $vatAmount;

    /** @var string Currency of VAT (based on the ISO 4217 currency code) */
    private $vatCurrency;

    /** @var double The amount of the fees */
    private $feesAmount;

    /** @var string Currency of fees (based on the ISO 4217 currency code) */
    private $feesCurrency;

    /** @var double Amount received from the payment system after all fees */
    private $payoutAmount;

    /** @var string Currency of payout (based on the ISO 4217 currency code) */
    private $payoutCurrency;

    /** @var double Exchange rate if the currency of the invoice and the payment received differ */
    private $payoutExchangeRate;

    /** @var string Language of user (based on the ISO 639-2 code) */
    private $userLang;

    /** @var array List of user emails */
    private $userEmails;

    /** @deprecated */
    private $paymentDate;

    /** @var array List of user phones */
    private $userPhones;

    /** @var array List of user accounts */
    private $userAccounts;

    /** @var string User country (based on the ISO 3166 alpha-3 country codes) */
    private $userCountry;

    /** @var string IP address of user (Automatically populated by $_SERVER properties) */
    private $userIp;

    /** @var string Contains all query parameters when creating the Event (Create automatically by $_REQUEST properties) */
    private $originalRequest;

    /** @var array Any extra params */
    private $extra;

    public function __construct()
    {
        $this->transactionId = '';
        $this->merchantTransactionId = '';
        $this->event = '';
        $this->dataSource = '';
        $this->dataChannel = self::DATA_CHANNEL;
        $this->isTest = false;
        $this->eventUrl = $this->resolveEventUrl($_SERVER);
        $this->paymentSystem = '';
        $this->paymentMethod = '';
        $this->paymentSubMethod = '';
        $this->paymentAmount = 0;
        $this->paymentCost = 0;
        $this->paymentCurrency = '';
        $this->paymentChannel = '';
        $this->vatAmount = 0;
        $this->vatCurrency = '';
        $this->feesAmount = 0;
        $this->feesCurrency = '';
        $this->payoutAmount = 0;
        $this->payoutCurrency = '';
        $this->payoutExchangeRate = 1;
        $this->userLang = '';
        $this->userCountry = '';
        $this->paymentDate = '';
        $this->userEmails = [];
        $this->userPhones = [];
        $this->userAccounts = [];
        $this->userIp = $this->resolveUserIp($_SERVER);
        $this->extra = [];
        $this->originalRequest = $_REQUEST ? json_encode($_REQUEST) : '';
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
     * @return Event
     */
    public function setTransactionId($transactionId)
    {
        if (!preg_match('/([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})/', $transactionId)) {
            throw new InvalidArgumentException('Invalid transaction identifier');
        }

        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantTransactionId()
    {
        return $this->merchantTransactionId;
    }

    /**
     * @param string $merchantTransactionId
     * @return Event
     */
    public function setMerchantTransactionId($merchantTransactionId)
    {
        $this->merchantTransactionId = $merchantTransactionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string
     * @return Event
     */
    public function setEvent($event)
    {
        if ($event && !in_array($event, [self::EVENT_START, self::EVENT_SUCCESS, self::EVENT_FAIL])) {
            throw new InvalidArgumentException('Invalid event name');
        }

        $this->event = $event;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param string $dataSource
     * @return Event
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTest()
    {
        return $this->isTest;
    }

    /**
     * @param bool $isTest
     * @return Event
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventUrl()
    {
        return $this->eventUrl;
    }

    /**
     * @param string
     * @return Event
     */
    public function setEventUrl($eventUrl)
    {
        $this->eventUrl = $eventUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentSystem()
    {
        return $this->paymentSystem;
    }

    /**
     * @param string $paymentSystem
     * @return Event
     */
    public function setPaymentSystem($paymentSystem)
    {
        $this->paymentSystem = $paymentSystem;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     * @return Event
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentSubMethod()
    {
        return $this->paymentSubMethod;
    }

    /**
     * @param string $paymentSubMethod
     * @return Event
     */
    public function setPaymentSubMethod($paymentSubMethod)
    {
        $this->paymentSubMethod = $paymentSubMethod;
        return $this;
    }

    /**
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * @param float $paymentAmount
     * @return Event
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = $paymentAmount;
        return $this;
    }

    /**
     * @return float
     */
    public function getPaymentCost()
    {
        return $this->paymentCost;
    }

    /**
     * @param float $paymentCost
     * @return Event
     */
    public function setPaymentCost($paymentCost)
    {
        $this->paymentCost = $paymentCost;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentCurrency()
    {
        return $this->paymentCurrency;
    }

    /**
     * @param string $paymentCurrency
     * @return Event
     */
    public function setPaymentCurrency($paymentCurrency)
    {
        $this->paymentCurrency = $paymentCurrency;
        return $this;
    }

    /**
     * @return float
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @param float $vatAmount
     * @return Event
     */
    public function setVatAmount($vatAmount)
    {
        $this->vatAmount = $vatAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getVatCurrency()
    {
        return $this->vatCurrency;
    }

    /**
     * @param string $vatCurrency
     * @return Event
     */
    public function setVatCurrency($vatCurrency)
    {
        $this->vatCurrency = $vatCurrency;
        return $this;
    }

    /**
     * @return float
     */
    public function getFeesAmount()
    {
        return $this->feesAmount;
    }

    /**
     * @param float $feesAmount
     * @return Event
     */
    public function setFeesAmount($feesAmount)
    {
        $this->feesAmount = $feesAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getFeesCurrency()
    {
        return $this->feesCurrency;
    }

    /**
     * @param string $feesCurrency
     * @return Event
     */
    public function setFeesCurrency($feesCurrency)
    {
        $this->feesCurrency = $feesCurrency;
        return $this;
    }

    /**
     * @return float
     */
    public function getPayoutAmount()
    {
        return $this->payoutAmount;
    }

    /**
     * @param float $payoutAmount
     * @return Event
     */
    public function setPayoutAmount($payoutAmount)
    {
        $this->payoutAmount = $payoutAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getPayoutCurrency()
    {
        return $this->payoutCurrency;
    }

    /**
     * @param string $payoutCurrency
     * @return Event
     */
    public function setPayoutCurrency($payoutCurrency)
    {
        $this->payoutCurrency = $payoutCurrency;
        return $this;
    }

    /**
     * @return float
     */
    public function getPayoutExchangeRate()
    {
        return $this->payoutExchangeRate;
    }

    /**
     * @param float $payoutExchangeRate
     * @return Event
     */
    public function setPayoutExchangeRate($payoutExchangeRate)
    {
        $this->payoutExchangeRate = $payoutExchangeRate;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserLang()
    {
        return $this->userLang;
    }

    /**
     * @param string $userLang
     * @return Event
     */
    public function setUserLang($userLang)
    {
        $this->userLang = $userLang;
        return $this;
    }

    /**
     * @return array
     */
    public function getUserEmails()
    {
        return $this->userEmails;
    }

    /**
     * @param array $userEmails
     * @return Event
     */
    public function setUserEmails($userEmails)
    {
        $this->userEmails = $userEmails;
        return $this;
    }

    /**
     * @return array
     */
    public function getUserPhones()
    {
        return $this->userPhones;
    }

    /**
     * @param array $userPhones
     * @return Event
     */
    public function setUserPhones($userPhones)
    {
        $this->userPhones = $userPhones;
        return $this;
    }

    /**
     * @return array
     */
    public function getUserAccounts()
    {
        return $this->userAccounts;
    }

    /**
     * @param array $userAccounts
     * @return Event
     */
    public function setUserAccounts($userAccounts)
    {
        $this->userAccounts = $userAccounts;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * @param string
     * @return Event
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalRequest()
    {
        return $this->originalRequest;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     * @return Event
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * Create Event from array
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $obj = new self();

        foreach ($data as $key => $value) {
            $setterName = sprintf('set%s', $key);

            if (method_exists($obj, $setterName)) {
                $obj->$setterName($value);
            }
        }

        return $obj;
    }

    /**
     * Return Event as array
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    private function resolveEventUrl($server)
    {
        $ssl = (!empty($server['HTTPS']) && $server['HTTPS'] == 'on');
        $sp = isset($server['SERVER_PROTOCOL']) ? strtolower($server['SERVER_PROTOCOL']) : '';
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = isset($server['SERVER_PORT']) ? $server['SERVER_PORT'] : '';
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = (isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : null);
        $host = isset($host) ? $host : isset($server['SERVER_NAME']) ? $server['SERVER_NAME'] . $port : '';

        if (empty($protocol) && empty($host)) {
            return '';
        }

        return $protocol . '://' . $host . $server['REQUEST_URI'];
    }

    private function resolveUserIp($server)
    {
        if (!empty($server['HTTP_CLIENT_IP'])) {
            return $server['HTTP_CLIENT_IP'];
        }
        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            return $server['HTTP_X_FORWARDED_FOR'];
        }
        if (!empty($server['REMOTE_ADDR'])) {
            return $server['REMOTE_ADDR'];
        }

        return '';
    }
}
