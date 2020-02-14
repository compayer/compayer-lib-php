<?php

class PaysuperAnalyticsConformDataYamoney
{

	public static $paymentMethods = array(
		'AC' => "Bank cards",
		'PC' => "E-payments",
		'SB' => "Bank Transfer",
		'WM' => "E-payments",
		'AB' => "Bank Transfer",
		'QW' => "E-payments",
		'MC' => "Mobile Payments",
		'PB' => "Bank Transfer",
	);

	public static $paymentSubMethods = array(
		'AC' => "",
		'PC' => "Yandex.Money",
		'SB' => "Sberbank Online",
		'WM' => "WebMoney",
		'AB' => "Alfa Click",
		'QW' => "QIWI",
		'MC' => "Mobile payment",
		'PB' => "Promsvyazbank",
	);

	public static function GetData($request = array())
	{
		if (empty($request['paymentType']))
		{
			return null;
		}

		$paymentMethod = self::$paymentMethods[$request['paymentType']];

		$cardPanMask = '';
		$paymentSubMethod = "";
		if ($request['paymentType'] === "AC")
		{
			if (empty($request['cdd_pan_mask']))
			{
				return null;
			}

			$cardPanMask = $request['cdd_pan_mask'];
		} else
		{
			$paymentSubMethod = self::$paymentSubMethods[$request['paymentType']];
		}

		$paymentAmount = $request['orderSumAmount'] * 1;
		$paymentCost = ($request['orderSumAmount'] * 1 - $request['shopSumAmount'] * 1);

		$userLang = '';
		if (!empty($request['lang']))
		{
			$userLang = $request['lang'];
		} elseif (!empty($request['language']))
		{
			$userLang = $request['language'];
		}

		$accounts = new stdClass();
		if (!empty($request['alfa_client_id']))
		{
			$accounts->alfaclick = $request['alfa_client_id'];
		}
		if (!empty($request['uid']) && $request['uid'] != "0")
		{
			$accounts->yandex_uid = $request['uid'];
		}
		if (!empty($request['yandexuid']))
		{
			$accounts->yandex_yuid = $request['yandexuid'];
		}

		return array(
			"paymentSystem" => "Yandex.Money",
			"paymentMethod" => $paymentMethod,
			"paymentSubMethod" => $paymentSubMethod,
			"paymentAmount" => $paymentAmount,
			"paymentCost" => $paymentCost,
			"paymentCurrency" => "RUB",
			"vatAmount" => 0,
			"vatCurrency" => "RUB",
			"feesAmount" => $paymentCost,
			"feesCurrency" => "RUB",
			"payoutAmount" => $paymentAmount - $paymentCost,
			"payoutCurrency" => "RUB",
			"payoutExchangeRate" => 1,
			"userLang" => $userLang,
			"userPhones" => array_filter(array($request['phone'], $request['phoneNumber'], $request['yandexPhoneNumber'])),
			"userEmails" => array_filter(array($request['email'])),
			"userAccounts" => $accounts,
			"userCountry" => $request['cps_user_country_code'],
			"merchantTransactionId" => $request['unilabel'],
			"customerNumber" => $request['customerNumber'],
			"cardPanMask" => $cardPanMask,
		);
	}
}