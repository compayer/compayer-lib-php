# paysuper-analytics-lib-php

Library for push stat message to Paysuper Analytics from php-bases projects.

Example to use:

```php
require_once('PaysuperAnalyticsLib.php');
require_once('PaysuperAnalyticsConformDataYamoney.php');

$event = 'start'; // 'start', 'success', 'fail'

$accounts = new stdClass();
$accounts->yourproject = "131312323213132410";

$data = array(
	"paymentSystem" => "Yandex.Money",  // 'Paysuper', 'Yandex.Money', 'Xsolla'
	"paymentMethod" => "Bank cards", // 'Bank cards', 'Bank Transfer', 'Cash Payments', 'Cryptocurrency', 'E-payments', 'Mobile Payments', 'Prepaid Cards'
	"paymentSubMethod" => "Visa",
	"paymentAmount" => 75,
	"paymentCost" => 2.63,
	"paymentCurrency" => "RUB",
	"vatAmount" => 0,
	"vatCurrency" => "",
	"feesAmount" => 2.63,
	"feesCurrency" => "RUB",
	"payoutAmount" => 72.37,
	"payoutCurrency" => "RUB",
	"payoutExchangeRate" => 0,
	"userLang" => "RU",
	"userPhones" => array("79111236547"),
	"userEmails" => array("test@test.com"),
	"userAccounts" => $accounts,
	"userCountry" => "RU",
	"merchantTransactionId" => "some id",
);

if (!defined('PAYSUPER_ANALYTICS_IS_PROD'))
{
	define('PAYSUPER_ANALYTICS_IS_PROD', true);
}
if (!defined('PAYSUPER_ANALYTICS_DATA_SOURCE'))
{
	define('PAYSUPER_ANALYTICS_DATA_SOURCE', 'yourproject');
}

PaysuperAnalyticsLib::Push(PAYSUPER_ANALYTICS_IS_PROD, PAYSUPER_ANALYTICS_DATA_SOURCE, $event, $data);
```

Example to use with helper

```php
require_once('PaysuperAnalyticsLib.php');
require_once('PaysuperAnalyticsConformDataYamoney.php');

$request = array(); //  response from payment system 

$data = PaysuperAnalyticsConformDataYamoney::GetData($request);
if (!is_null($data))
{
	$data['userAccounts']->yourproject = "131312323213132410";

	if (!defined('PAYSUPER_ANALYTICS_IS_PROD'))
	{
		define('PAYSUPER_ANALYTICS_IS_PROD', true);
	}
	if (!defined('PAYSUPER_ANALYTICS_DATA_SOURCE'))
	{
		define('PAYSUPER_ANALYTICS_DATA_SOURCE', 'yourproject');
	}

	$event = 'start'; // 'start', 'success', 'fail'

	PaysuperAnalyticsLib::Push(PAYSUPER_ANALYTICS_IS_PROD, PAYSUPER_ANALYTICS_DATA_SOURCE, $event, $data);
}
```