<?php

namespace Paysuper;

use Exception;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\unwrap;
use stdClass;
use GuzzleHttp\Middleware;
use Monolog\Logger;
use Monolog\Handler\SyslogHandler;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\HandlerStack;

class AnalyticsLib
{
	public static $version = '1.0.0';

	private static $testUrl = 'https://analytics.tst.protocol.one/push';

	private static $prodUrl = 'https://analytics.pay.super.com/push';

    /**
     * payment order was created
     */
	const EVENT_START = 'start';

    /**
     * payment was successfully completed
     */
    const EVENT_SUCCESS = 'success';

    /**
     * payment was failed
     */
    const EVENT_FAIL = 'fail';


    public static function Push($bIsProd = false, $dataSource = '', $event = '', $data = array())
	{
		$transactionId = self::getValue($data, 'transactionId', '');
		if (empty($transactionId))
		{
			if (isset($_COOKIE['psa_tid']))
			{
				$transactionId = $_COOKIE['psa_tid'];
			} else
			{
				try
				{
					$uuid4 = Uuid::uuid4();
					$transactionId = $uuid4->toString();
				} catch (Exception $e)
				{
					error_log('Caught exception: ' . $e->getMessage());
				}

				if ($event == self::EVENT_SUCCESS || $event == self::EVENT_FAIL)
				{
					unset($_COOKIE['psa_tid']);
					@setcookie("psa_tid", "", time() - 3600);
				} else
				{
					@setcookie('psa_tid', $transactionId);
				}
			}
		}

		if (empty($transactionId))
		{
			return;
		}

		$accounts = new stdClass();
		$accountsArr = self::getValue($data, 'userAccounts', array());
		if (sizeof($accountsArr) > 0)
		{
			foreach ($accountsArr as $key => $val)
			{
				$accounts->$key = $val;
			}
		}

		$normalizedKeys = array('transactionId', 'paymentSystem', 'paymentMethod',
			'paymentSubMethod', 'paymentChannel', 'paymentAmount', 'paymentCost', 'paymentCurrency',
			'vatAmount', 'vatCurrency', 'feesAmount', 'feesCurrency', 'payoutAmount', 'payoutCurrency',
			'payoutExchangeRate', 'userLang', 'paymentDate', 'userEmails', 'userPhones', 'userAccounts', 'userCountry',
			'userPhone', 'userEmail');

		$dataKeys = array_keys($data);

		$extraKeys = array_diff($dataKeys, $normalizedKeys);

		$extra = new stdClass();
		foreach ($extraKeys as $key)
		{
			$extra->$key = $data[$key];
		}

		$message = array(
			'transactionId' => $transactionId,
			'event' => $event,
			'eventUrl' => self::full_url($_SERVER),
			'paymentSystem' => self::getValue($data, 'paymentSystem', ''),
			'paymentMethod' => self::getValue($data, 'paymentMethod', ''),
			'paymentSubMethod' => self::getValue($data, 'paymentSubMethod', ''),
			'paymentChannel' => self::getValue($data, 'paymentChannel', ''),
			'paymentAmount' => self::getValue($data, 'paymentAmount', 0),
			'paymentCost' => self::getValue($data, 'paymentCost', 0),
			'paymentCurrency' => self::getValue($data, 'paymentCurrency', ''),
			'vatAmount' => self::getValue($data, 'vatAmount', 0),
			'vatCurrency' => self::getValue($data, 'vatCurrency', ''),
			'feesAmount' => self::getValue($data, 'feesAmount', 0),
			'feesCurrency' => self::getValue($data, 'feesCurrency', ''),
			'payoutAmount' => self::getValue($data, 'payoutAmount', 0),
			'payoutCurrency' => self::getValue($data, 'payoutCurrency', ''),
			'payoutExchangeRate' => self::getValue($data, 'payoutExchangeRate', 0),
			'userLang' => self::getValue($data, 'userLang', ''),
			'userEmails' => array_filter(self::getValue($data, 'userEmails', [])),
			'userPhones' => array_filter(self::getValue($data, 'userPhones', [])),
			'userCountry' => self::getValue($data, 'userCountry', ''),
			'userAccounts' => $accounts,
			'userIp' => self::getUserIp($_SERVER),
			'dataSource' => $dataSource,
			'dataChannel' => 'back',
			'originalRequest' => sizeof($_REQUEST) ? json_encode($_REQUEST) : '',
			'extra' => $extra,
		);

		$dataJson = json_encode($message);

		$url = $bIsProd ? self::$prodUrl : self::$testUrl;

		self::send($url, $dataJson);
	}

	private static function getValue($arr = array(), $key = '', $defaultVal = '')
	{
		if (empty($key) || !isset($arr[$key]))
		{
			return $defaultVal;
		}
		return $arr[$key];
	}

	private static function url_origin($s, $use_forwarded_host = false)
	{
		$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
		$sp = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port = $s['SERVER_PORT'];
		$port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
		$host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
		$host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
		if (empty($protocol) && empty($host))
		{
			return '';
		}
		return $protocol . '://' . $host;
	}

	private static function full_url($s, $use_forwarded_host = false)
	{
		$url = self::url_origin($s, $use_forwarded_host);
		if (empty($url))
		{
			return '';
		}

		return $url . $s['REQUEST_URI'];
	}

	private static function getUserIp($s)
	{
		if (!empty($s['HTTP_CLIENT_IP']))
		{
			return $s['HTTP_CLIENT_IP'];
		}
		if (!empty($s['HTTP_X_FORWARDED_FOR']))
		{
			return $s['HTTP_X_FORWARDED_FOR'];
		}
		if (!empty($s['REMOTE_ADDR']))
		{
			return $s['REMOTE_ADDR'];
		}

		return '';
	}

	private static function send($url = '', $data = '')
	{
        $client = new Client(
            [
                'verify' => false,
                'handler' => self::getHandlerStack(),
            ]
        );

		try {
			$headers = [
				'Content-Length' => mb_strlen($data),
				'Referer' => $_SERVER['HTTP_REFERER'],
				'Content-Type' => 'application/json',
				'User-Agent' => "ps-analytics-lib-php-" . self::$version,
			];

			$request = new Request('POST', $url, $headers, $data);
			$client->sendAsync($request);
		} catch (Throwable $e) {
			error_log('Analytics collector push error: ' . $e->getMessage());
		}
	}

    /**
     * @return HandlerStack
     */
    private static function getHandlerStack()
    {
        $name = 'paysuper';

        $logger = new Logger($name);
        $logger->pushHandler(new SyslogHandler($name));

        $formatter = new MessageFormatter('date: {date_iso_8601} '."\n".'request: '."\n".'{request}'."\n".'response: {response}'."\n");

        $stack = HandlerStack::create();
        $stack->push(Middleware::log($logger, $formatter));

        return $stack;
    }
}
