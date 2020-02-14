<?php

class PaysuperAnalyticsLib
{
	public static $version = '1.0.0';

	private static $testUrl = 'http://localhost/push';

	private static $prodUrl = 'https://analytics.protocol.one/push';

	private static $timeout = 1;

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
				$transactionId = self::get_guid();

				if ($event == 'success' || $event == 'fail')
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

		print_r($dataJson);

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

	// Get an RFC-4122 compliant globaly unique identifier
	private static function get_guid()
	{
		$data = PHP_MAJOR_VERSION < 7 ? openssl_random_pseudo_bytes(16) : random_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	private static function send($url = '', $data = '')
	{
		if (!function_exists('curl_init'))
		{
			error_log('curl_init function not exists');
			return;
		}

		$curl = @curl_init();

		// Предотвращаем chunked-ответ
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl, CURLOPT_HEADER, TRUE);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, TRUE);

		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$dataLen = mb_strlen($data);
		$aHeaders = array();
		$aHeaders[] = "Content-Length: " . $dataLen;
		$aHeaders[] = "Referer: " . $_SERVER['HTTP_REFERER'];
		$aHeaders[] = "Content-Type: application/json";

		curl_setopt($curl, CURLOPT_HTTPHEADER, $aHeaders);

		curl_setopt($curl, CURLOPT_TIMEOUT, self::$timeout);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$timeout);
		curl_setopt($curl, CURLOPT_USERAGENT, "ps-analytics-lib-php-" . self::$version);
		curl_setopt($curl, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);

		curl_setopt($curl, CURLOPT_VERBOSE, FALSE); // Minimize logs
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // No certificate
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // Return in string

		// Close connection
		//curl_setopt($curl, CURLOPT_FORBID_REUSE, TRUE);

		// TLS 1.2
		//curl_setopt($curl, CURLOPT_SSLVERSION, 6);

		// Get the target contents
		@curl_exec($curl);

		$_errno = curl_errno($curl);
		$_error = curl_error($curl);

		// Close PHP cURL handle
		@curl_close($curl);

		if ($_errno)
		{
			error_log('Analytics collector push error: ' . $_errno . ' ' . $_error);
		}
	}
}
