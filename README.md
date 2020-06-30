# Compayer PHP SDK

[![Latest Stable Version](https://poser.pugx.org/compayer/compayer-lib-php/v/stable.png)](https://packagist.org/packages/compayer/compayer-lib-php)
[![Build Status](https://travis-ci.org/compayer/compayer-lib-php.png?branch=master)](https://travis-ci.org/compayer/compayer-lib-php)
[![Code Coverage](https://codecov.io/gh/compayer/compayer-lib-php/branch/master/graph/badge.svg)](https://codecov.io/gh/compayer/compayer-lib-php)
[![Downloads](https://poser.pugx.org/compayer/compayer-lib-php/d/total.png)](https://packagist.org/packages/compayer/compayer-lib-php)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/compayer/compayer-lib-php/master/LICENSE)

Compayer is a stat data preprocessor and web analytics service that tracks customer events in payment forms for financial and marketing reports.

Compayer PHP SDK library is designed to push stat messages to the Compayer analytics from the php-based projects.

## Features
- Creates and sends the Events of start, success, failure or refund payments to the Compare analytics.
- Helps to convert a response message from payment systems Yandex.Money, Xsolla and PaySuper to the Event message. 

---

## Table of Contents

- [Requirements](#requirements)
- [Getting Started](#getting-started)
- [Installation](#installation)
- [Usage](#usage)

## Requirements
- PHP >= 5.5
- Required PHP extensions: *json*

## Getting Started

Register your account in [Compayer](https://compayer.com) analytics to get:
- CLIENT ID (the unique identifier for your client)
- SECRET KEY (the secret API key for your client)

## Installation

We recommend installing Compayer PHP SDK using [Composer](http://getcomposer.org).

``` bash
$ cd /path/to/your/project
$ composer require compayer/compayer-lib-php
```

After installing, you need to require the Composer's autoloader:

```php
require '/path/to/vendor/autoload.php';
```

## Usage

To use analytics you need to send 2 events: 
- The Event `start` when a user initiates a payment. The `start` event is optional, but we strongly recommend using it to track the entire payment chain. 
- One of the Events `success`, `fail` or `refund` after the payment system responds about the result of the operation.

The Event tries automatically determine the user IP address and address of the payment initiation page based on the data from the server request. 
If the user’s request is not available to the script, you can set the payment initiation page or user IP address by yourself (this is necessary for the geolocation filters to work correctly).

To send an Event `start`, use the following example:

```php
use Compayer\SDK\Client;
use Compayer\SDK\Config;
use Compayer\SDK\Event;
use Compayer\SDK\Exceptions\SdkException;

const CLIENT_ID = 'client_id';
const SECRET_KEY = 'secret_key';

// Create and configure a configuration object (including debug mode).
$config = new Config(CLIENT_ID, SECRET_KEY);
$config->setDebugMode(true);

// Create an SDK client for sending events.
$client = new Client($config);

// Create an instance of the Event class and set the maximum possible properties about a user and payment.
// All fields are optional, but it's important to fill out one of the fields: "userEmails", "userPhones" or "userAccounts" 
// to identify the user made the payment.
$event = (new Event())
    ->setMerchantTransactionId('12345')
    ->setPaymentAmount(250.50)
    ->setPaymentCurrency('RUB')
    ->setUserLang('RUS')
    ->setUserEmails(['customer@compayer.com'])
    ->setUserAccounts(['54321'])
    ->setExtra(['my_property' => 'value']);

// You can also create an object with an event from an array, 
// where names of the keys of the array match names of the Event properties.
$event = Event::fromArray([
    'merchantTransactionId' => '12345',
    'paymentAmount' => 250.50,
    'paymentCurrency' => 'RUB',
    'userLang' => 'RUS',
    'userEmails' => ['customer@compayer.com'],
    'userAccounts' => ['54321'],
    'extra' => ['my_property' => 'value'],
]);

try {
    // Send the generated event and get the response message with a transaction identifier and log.
    $response = $client->pushStartEvent($event);
} catch (SdkException $e) {
    print_r($e->getMessage());
}

// Use it to send "success", "fail" or "refund" events and to chain events.
// The transaction identifier is UUID string like 3677eb06-1a9a-4b6c-9d6a-1799cae1b6bb.
$transactionId = $response->getTransactionId();

// Show logs with the debug mode configuration.
print_r($response->getLog());
```

After a payment system has received a response about a payment result (`success`, `failure` or `refund`), you need to send an Event with the data that you received after the payment. 
You can form the response event as described in the Event `start`. If you received a transaction ID at the start step, set it to link the entire payment chain.

For `success`, `failure` or `refund` events a payment system response is required in its original form.
The response should be written as a string with the key "response" in the property `extra`.

For example, if the answer received in the JSON format then use the construct: `setPaymentSystemResponse(json_encode($jsonPaymentSystemResponse))`.

```php
use Compayer\SDK\Client;
use Compayer\SDK\Config;
use Compayer\SDK\Event;
use Compayer\SDK\Exceptions\SdkException;

const CLIENT_ID = 'client_id';
const SECRET_KEY = 'secret_key';

// Create and configure a configuration object (e.g. with debug mode).
$config = new Config(CLIENT_ID, SECRET_KEY);
$config->setDebugMode(true);

// Create SDK client for sending events.
$client = new Client($config);

// Transaction ID received on start event
$transactionId = '3677eb06-1a9a-4b6c-9d6a-1799cae1b6bb';

// Create an instance of the Event class and set the maximum possible properties about the user and payment
// All fields are optional, but you must fill out one of the fields: "userEmails", "userPhones" or "userAccounts" 
// to identify the user who made the payment. If you have a transaction ID for the start event, specify it.
$event = (new Event())
    ->setTransactionId($transactionId)
    ->setMerchantTransactionId('12345')
    ->setPaymentAmount(250.50)
    ->setPaymentCurrency('RUB')
    ->setPayoutAmount(3.87)
    ->setPayoutCurrency('USD')
    ->setUserEmails(['customer@compayer.com'])
    ->setUserAccounts(['54321'])
    ->setExtra(['my_property' => 'value'])
    ->setPaymentSystemResponse('Payment system response as a string');

try {
    // Send the generated event
    // Or use $client->pushFailEvent($event) in case of payment failure
    // Or use $client->pushRefundEvent($event) in case of payment refund
    $client->pushSuccessEvent($event);
} catch (SdkException $e) {
    print_r($e->getMessage());
}
```

## License

The project is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT).
