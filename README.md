# compayer-lib-php

Library for push stat message to Compayer from php-bases projects.

Example to use:

```php
use Compayer\CompayerLib;

$accounts = new stdClass();
$accounts->yourproject = "131312323213132410";

$data = array(
	"paymentSystem" => "Yandex.Money",  // 'Compayer', 'Yandex.Money', 'Xsolla'
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

if (!defined('COMPAYER_IS_PROD')) {
	define('COMPAYER_IS_PROD', true);
}

if (!defined('COMPAYER_DATA_SOURCE')) {
	define('COMPAYER_DATA_SOURCE', 'yourproject');
}

$event = CompayerLib::EVENT_START; // use correct event name here

CompayerLib::Push(COMPAYER_IS_PROD, COMPAYER_DATA_SOURCE, $event, $data);
```

Example to use with helper

```php
use Compayer\CompayerConformDataYamoney;
use Compayer\CompayerLib;

$request = array(); //  response from payment system 
$data = CompayerConformDataYamoney::GetData($request);

if ($data !== null) {
	$data['userAccounts']->yourproject = "131312323213132410";

	if (!defined('COMPAYER_IS_PROD')) {
		define('COMPAYER_IS_PROD', true);
	}
	
    if (!defined('COMPAYER_DATA_SOURCE')) {
		define('COMPAYER_DATA_SOURCE', 'yourproject');
	}

	$event = CompayerLib::EVENT_START; // use correct event name here

	CompayerLib::Push(COMPAYER_IS_PROD, COMPAYER_DATA_SOURCE, $event, $data);
}
```
