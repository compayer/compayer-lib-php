# Compayer SDK for PHP

[![Latest Stable Version](https://poser.pugx.org/compayer/compayer-lib-php/v/stable.png)](https://packagist.org/packages/compayer/compayer-lib-php)
[![Build Status](https://travis-ci.org/compayer/compayer-lib-php.png?branch=master)](https://travis-ci.org/compayer/compayer-lib-php)
[![Code Coverage](https://codecov.io/gh/compayer/compayer-lib-php/branch/master/graph/badge.svg)](https://codecov.io/gh/compayer/compayer-lib-php)
[![Downloads](https://poser.pugx.org/compayer/compayer-lib-php/d/total.png)](https://packagist.org/packages/compayer/compayer-lib-php)

An official PHP SDK library for push stat message to Compayer.

## Features
* Creates and send the Event of start, success and fail to the Compare analytics.
* Help to convert response message from Yandex.Money, Xsolla and Paysuper to the Event message. 

## Requirements
* PHP >=5.5
* The following PHP extensions required:
  * json

## Getting Started

Register your account in [Compayer](https://compayer.com) analytics and create the data source.
In order to use the PHP SDK Library you'll need:
* DATA SOURCE identifier
* SECRET KEY of data source

## Installation

### Installing via Composer

The recommended way to install Compayer SDK for PHP is through [Composer](http://getcomposer.org).

``` bash
$ cd /path/to/your/project
$ composer require compayer/compayer-lib-php
```

After installing, you need to require Composer's autoloader:

```php
require '/path/to/vendor/autoload.php';
```

## Quick Examples

For the analytics to work better, you need to send 2 events: “start” when the user initiates payments and “success” or 
“fail” (after the payment system responds about the result of the operation).
The "start" event is optional, but we strongly recommend using it to track the entire payment chain. 

The event tries to determine the ip address of the user and the address of the payment initiation page automatically, 
based on data from the server request. If the user’s request is not available to the script, you can set the payment 
page or the user’s IP address yourself (this is necessary for the geolocation filters to work correctly).

To send a start event, use the following example:

```php
use Compayer\SDK\Client;
use Compayer\SDK\Event;

const DATA_SOURCE = 'data_source_id';
const SECRET_KEY = 'secret_key';

// Initialization of the client for sending events.
// Available configuration parameters can be found in the class constants Client::CONFIG_*.
$client = Client::create([
    Client::CONFIG_DATA_SOURCE => DATA_SOURCE,
    Client::CONFIG_SECRET_KEY => SECRET_KEY,
]);

// Create an instance of the Event class and set the maximum possible properties about the user and payment.
// All fields are optional, but you must fill out one of the fields: "userEmails", "userPhones" or "userAccounts" 
// to identify the user who made the payment.
$event = (new Event())
    ->setMerchantTransactionId('12345')
    ->setPaymentAmount(250.50)
    ->setPaymentCurrency('RUB')
    ->setUserLang('RUS')
    ->setUserEmails(['customer@compayer.com'])
    ->setUserAccounts(['54321'])
    ->setExtra(['my_property' => 'value']);

// You can also create an object with an event from an array, 
// where the names of the keys of the array match the names of the Event properties.
$event = Event::fromArray([
    'merchantTransactionId' => '12345',
    'paymentAmount' => 250.50,
    'paymentCurrency' => 'RUB',
    'userLang' => 'RUS',
    'userEmails' => ['customer@compayer.com'],
    'userAccounts' => ['54321'],
    'extra' => ['my_property' => 'value'],
]);

// Send the generated event and get the generated transaction identifier.
// Use it to send "success" and "fail" events to chain events.
// Transaction identifier is UUID and is a string like 3677eb06-1a9a-4b6c-9d6a-1799cae1b6bb.
$transactionId = $client->pushStartEvent($event);
```

After the payment system has received a response about the payment result (success or failure), it is necessary to send another event.
Form the event as described in the start event. Enrich the event with the data that you received after payment.
If at the start step you received a transaction ID, set it to link the entire payment chain.

For events of "success" and "fail", a payment system response is required in its original form.
The response should be written as a string with the key "response" in the property "extra".

```php
use Compayer\SDK\Client;
use Compayer\SDK\Event;

const DATA_SOURCE = 'data_source_id';
const SECRET_KEY = 'secret_key';

// Transaction ID received on start event
$transactionId = '3677eb06-1a9a-4b6c-9d6a-1799cae1b6bb';

// Initialization of the client for sending events.
// Available configuration parameters can be found in the class constants Client::CONFIG_*.
$client = Client::create([
    Client::CONFIG_DATA_SOURCE => DATA_SOURCE,
    Client::CONFIG_SECRET_KEY => SECRET_KEY,
]);

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
    ->setExtra([
        'my_property' => 'value',
        Event::EXTRA_RESPONSE => 'Payment system response as a string',
    ]);

// Send the generated event
// Or use $client->pushFailEvent($event) in case of payment failure
$client->pushSuccessEvent($event);
```
