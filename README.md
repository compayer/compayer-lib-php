# Compayer PHP Library

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)

Compayer is a stat data preprocessor and web analytics service that tracks customer events in payment forms for financial and marketing reports.

Compayer PHP Library is designed to push stat messages to PaySuper Analytics from the php-based projects.

| |The Compayer Architecture|
|---|---|
|**Accepting data**|[Compayer Receiver](https://github.com/compayer/compayer-receiver) is a microservice that accepts the events with data to send them to the Rabbit queue and logs.|
|**Storing data**|[Compayer Collector](https://github.com/compayer/compayer-collector) is a microservice that processes data and stores in a uniform format in the database.|
|**Creating reports**|[Compayer Reporter](https://github.com/compayer/compayer-reporter) is an API backend for [Compayer Portal](https://github.com/compayer/compayer-portal) that reads data from the database for building all kinds of reports.|
|**Displaying reports**|[Compayer Portal](https://github.com/compayer/compayer-portal) is a frontend to represent the reports.|
|**Storing user accounts**|[Compayer Accounts](https://github.com/compayer/compayer-accounts) is a microservice that stores the Compayer user accounts and performs a user registration and authentication.|

---

## Table of Contents

- [Usage](#usage)

## Usage

Example to use:

```php
use Paysuper\AnalyticsLib;

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

if (!defined('PAYSUPER_ANALYTICS_IS_PROD')) {
	define('PAYSUPER_ANALYTICS_IS_PROD', true);
}

if (!defined('PAYSUPER_ANALYTICS_DATA_SOURCE')) {
	define('PAYSUPER_ANALYTICS_DATA_SOURCE', 'yourproject');
}

$event = AnalyticsLib::EVENT_START; // use correct event name here

AnalyticsLib::Push(PAYSUPER_ANALYTICS_IS_PROD, PAYSUPER_ANALYTICS_DATA_SOURCE, $event, $data);
```

Example to use with the Helper:

```php
use Paysuper\AnalyticsConformDataYamoney;
use Paysuper\AnalyticsLib;

$request = array(); //  response from payment system 
$data = AnalyticsConformDataYamoney::GetData($request);

if ($data !== null) {
	$data['userAccounts']->yourproject = "131312323213132410";

	if (!defined('PAYSUPER_ANALYTICS_IS_PROD')) {
		define('PAYSUPER_ANALYTICS_IS_PROD', true);
	}
	
    if (!defined('PAYSUPER_ANALYTICS_DATA_SOURCE')) {
		define('PAYSUPER_ANALYTICS_DATA_SOURCE', 'yourproject');
	}

	$event = AnalyticsLib::EVENT_START; // use correct event name here

	AnalyticsLib::Push(PAYSUPER_ANALYTICS_IS_PROD, PAYSUPER_ANALYTICS_DATA_SOURCE, $event, $data);
}
```

## License

The project is available as open source under the terms of the [Apache-2.0 License](https://opensource.org/licenses/Apache-2.0).