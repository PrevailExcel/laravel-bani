# laravel-bani

[![Latest Stable Version](https://poser.pugx.org/prevailexcel/laravel-bani/v/stable.svg)](https://packagist.org/packages/prevailexcel/laravel-bani)
[![License](https://poser.pugx.org/prevailexcel/laravel-bani/license.svg)](LICENSE.md)
> A Laravel Package for working with Bani Payments seamlessly.
##
This package also allows you to receive webhooks from [Bani](https://bani.africa) which it verifies for you and processes the payloads.
It also implements The Bani Pop Payment Widget and handles the callback for laravel. You can start collecting payment in fiat and crypto payments in minutes.
## Installation

[PHP](https://php.net) 5.4+ or [HHVM](http://hhvm.com) 3.3+, and [Composer](https://getcomposer.org) are required.

To get the latest version of Laravel Bani, simply require it

```bash
composer require prevailexcel/laravel-bani
```

Or add the following line to the require block of your `composer.json` file.

```
"prevailexcel/laravel-bani": "1.0.*"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.



Once Laravel Bani is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key. 
> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/PrevailExcel/laravel-bani#configuration)

```php
'providers' => [
    ...
    PrevailExcel\Bani\BaniServiceProvider::class,
    ...
]
```

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Bani' => PrevailExcel\Bani\Facades\Bani::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="PrevailExcel\Bani\BaniServiceProvider"
```

A configuration-file named `bani.php` with some sensible defaults will be placed in your `config` directory:

```php
<?php

return [

    /**
     * Tribe Account Ref Key From Bani Dashboard
     *
     */
    'tribeAccountRef' => getenv('BANI_TRIBE_ACCOUNT_REF'),

    /**
     * Public Key From Bani Dashboard
     *
     */
    'publicKey' => getenv('BANI_PUBLIC_KEY'),

    /**
     * Merchant Key From Bani Dashboard
     *
     */
    'merchantKey' => getenv('BANI_PRIVATE_KEY'),

    /**
     * Merchant Key From Bani Dashboard
     *
     */
    'accessToken' => getenv('BANI_ACCESS_TOKEN'),

    /**
     * You enviroment can either be live or stage.
     * Make sure to add the appropriate API key after changing the enviroment in .env
     *
     */
    'env' => env('BANI_ENV', 'stage'),

    /**
     * BANI Live URL
     *
     */
    'liveUrl' => env('BANI_LIVE_URL', "https://live.getbani.com/api/v1"),

    /**
     * BANI Stage URL
     *
     */
    'stageUrl' => env('BANI_STAGE_URL', "https://stage.getbani.com/api/v1"),
];
```
## General payment flow
This is how the payment flow should be like:

### 1. Collect Customer Order Data
You have to collect necessary information from the user about what they are paying for and the amount. These usually include Email, Amount, Phone Number, First name and last name.
You can do this from your blade form directly (if you're building a website) or from the client app via a request.

### 2. Get the Bani Pop Widget or call the methods you want.
Bani allows users to pay without leaving your website or app. So you can call the `payWithWidget()` to get the Bani Pop Widget and your user can complete payment.

If you want to implement your own UI or you're building an API, you can use a variety  of  fluent methods you can use to start and complete the payment. You can use `bankTransfer()`,`mobileMoney()`,`payWithCrypto()`,`startPayWithOpay()` and even more.

### 3. You service the customer after payment
After having completed payment, you have to confirm the payment. This package helps handle webhook in a breeze. It also helps you handle callback if you use the pop widget.

As recommended, use the webhook response which this package verifies for you and then mark their order as paid and send them to a thank you page, send an email or whatever you want to do.

## Usage

Open your .env file and add all the necessary keys like so:

```php
BANI_TRIBE_ACCOUNT_REF=**-**************************
BANI_PUBLIC_KEY=***_****_*********************
BANI_PRIVATE_KEY=**********************
BANI_ACCESS_TOKEN=****************************************************
BANI_ENV=stage
```
*If you are using a hosting service like heroku, ensure to add the above details to your configuration variables.*
*Remember to change BANI_ENV to 'live' and update the keys when you are in production*

#### Next, you have to setup your routes. 
There are 3 routes you should have to get started.
1. To initiate payment
2. To setup callback (if you want to use the `payWithWidget()`) - Route::callback.
3. To setup webhook and handle the event responses - Route::webhook.

```php
// Laravel 5.1.17 and above
Route::post('/pay', 'PaymentController@createPayment')->name('pay');
Route::callback(PaymentController::class, 'handleGatewayCallback');
Route::webhook(WebhookController::class, 'handleWebhook');
```
OR

```php
// Laravel 8 & 9
Route::post('/pay', [PaymentController::class, 'createPayment'])->name('pay');
Route::callback(PaymentController::class, 'handleGatewayCallback');
Route::webhook(WebhookController::class, 'handleWebhook');
```

### Lets pay with widget now.
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PrevailExcel\Bani\Facades\Bani;

class PaymentController extends Controller
{  
    /**
     * You collect data from your blade form
     * and this returns the Bani Pop Widget
     */
    public function createPayment()
    {
        try {
            return Bani::payWithwidget();
        } catch (\Exception $e) {
            return redirect()->back()->withMessage(['msg' => $e->getMessage(), 'type' => 'error']);
        }
    }
    
    public function handleGatewayCallback()
    {
        // verify transaction and get data
        $data = bani()->getPaymentData();

        // Do anything you want
        dd($data);
    }
}
```
This opens the widget and your user completes payment. The packages redirects to the `handleGatewayCallback()` which veifies the payment and then you can use the payment data.

> To test with bank transfer, after selecting bank, copy the account and head to https://demo-checkout.getbani.com/test_bank/ to make test payment and then come back to your site and click on "I've paid {amount}" button.


### Lets pay with bank transfer now.
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PrevailExcel\Bani\Facades\Bani;

class PaymentController extends Controller
{  
    /**
     * You collect data from your blade form
     * and this returns the Account details for payment
     */
    public function createPayment()
    {
        try {
                $data = [
                    "pay_amount" => 10000,
                    "holder_account_type" => "temporary",
                    "customer_ref" => "MC-69941173958782368244",
                    "custom_data" => ["id" => 1, "color" => "white"],
                ];
                
                // You can use the global helper bani()>method() or the Facade Bani:: method().
                return bani()->bankTransfer($data);
        } catch (\Exception $e) {
            return redirect()->back()->withMessage(['msg' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function handleWebhook()
    {
        // verify webhook and get data
        bani()->getWebhookData()->proccessData(function ($data) {
            // Do something with $data
            logger($data);
            // If you have heavy operations, dispatch your queued jobs for them here
            // OrderJob::dispatch($data);
        });
        
        // Acknowledge you received the response
        return http_response_code(200);
    }
}
```
> To get customer ref, you can create the `bani()->createCustomer($userdata)` method wth user details or find the user via phone `bani()->customer("+2348011111111")` to get the user details.

This will return data that includes the account details which you will display or send to your user to make payment.
You can listen to the webhook and service the user. Write the heavy operations inside the `handleWebhook()` method.

> This package recommends to use a queued job to proccess the webhook data especially if you handle heavy operations like sending mail and more 

##### How does the webhook routing `Route::webhook(Controller::class, 'methodName')` work?

Behind the scenes, by default this will register a POST route `'bani/webhook'` to the controller and method you provide. Because the app that sends webhooks to you has no way of getting a csrf-token, you must add that route to the except array of the VerifyCsrfToken middleware:
```php
protected $except = [
    'bani/webhook',
];
```
#### A sample form will look like so:
```blade
<form method="POST" action="{{ route('pay') }}">
    @csrf
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="phone-number">Phone Number</label>
        <input class="form-control" type="tel" name="phone" required />
    </div>
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="email">Email</label>
        <input class="form-control" type="email" name="email" required />
    </div>
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="amount">Amount</label>
        <input class="form-control" type="number" name="amount" required />
    </div>
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="first-name">First Name</label>
        <input class="form-control" type="text" name="firstName" />
    </div>
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="last-name">Last Name</label>
        <input class="form-control" type="text" name="lastName" />
    </div>
    <div class="form-submit">
        <button class="btn btn-primary btn-block" type="submit"> Pay </button>
    </div>
</form>
```
When clicking the submit button the customer gets redirected to the Pop Widget.

So now the customer did some actions there (hopefully he or she paid the order) and now the package will redirect the customer to the Callback URL `Route::callback()`.

We must validate if the redirect to our site is a valid request (we don't want imposters to wrongfully place non-paid order).

In the controller that handles the request coming from the payment provider, we have

`Bani::getPaymentData()` - This function calls the `verifyTransaction()` methods and ensure it is a valid transaction else it throws an exception.

### Some fluent methods this package provides are listed here.

#### Customer

```php
// For all methods, you can get data from request()

/**
 * Check if the customer already exist
 * @returns array
 */
Bani::customer(?string $phone, ?string $ref);
// Or
request()->phone;
bani()->customer();


/**
 * Create a customer object
 * @returns array
 */
Bani::createCustomer();
// Or
bani()->createCustomer();


/**
 * Update a customer object
 * @returns array
 */
Bani::updateCustomer();
// Or
bani()->updateCustomer();


/**
 * Fetch your customer delivery/billing address(es).
 */
Bani::listBillingAddress();
// Or
bani()->listBillingAddress();


/**
 * Add customer delivery/billing address(es)
 * @returns array
 */
Bani::addBillingAddress();
// Or
bani()->addBillingAddress();


/**
 * Uupdate customer's delivery/billing address
 * @returns array
 */
Bani::updateBillingAddress();
// Or
bani()->updateBillingAddress();


/**
 * Delete customer's delivery/billing address
 * @returns array
 */
Bani::deleteBillingAddress();
// Or
bani()->deleteBillingAddress();
```
#### Collecting Payment

```php
/**
 * Accept payments from customers via bank transfer
 * @returns array
 */
Bani::bankTransfer();

/**
 * Accept payments via mobile money from customers
 * @returns array
 */
Bani::mobileMoney();

/**
 * Accept payments from customers via crypto and get settled in fiat
 * @returns array
 */
Bani::payWithCrypto();

/**
 * generate a deposit address to accept strictly DLT payments 
 * and get settled with the same currency
 */
Bani::getWalletAddress();

/**
 * Accept one-click payment from customers via bani shopper wallet
 * @returns array
 */
Bani::startPayWithBaniShopper();

/**
 * Complete 2nd step in payment from customers via bani shopper wallet
 * @returns array
 */
Bani::completePayWithBaniShopper();
```
#### Verification

```php
/**
 * The fastest and most secure way to verify mobile numbers using USSD instead of SMS OTPs.
 * @returns array
 */
Bani::verifyPhone(?string $phone);

/**
 * This endpoint is used to confirmed if the customer phone number has been verified
 * @returns array
 */
Bani::checkUssdStatus(?string $verification_reference);
```
#### Misc

```php
/**
 * This endpoint can be used to carry out a lookup on customer account
 * @returns array
 */
Bani::confirmEwallet($wallet, $tag, ?string $type);
```

## Todo

* Add Comprehensive Tests
* Add Support For Agent Endpoints
* Add Support For More Misc Endpoints
* Add Support For Bill Payment Endpoints
* Add Support For Payoutt Endpoints

## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## How can I thank you?

Why not star the github repo? I'd love the attention! Why not share the link for this repository on Twitter or HackerNews? Spread the word!

Don't forget to [follow me on Twitter](https://twitter.com/EjimaduPrevail)! and also  [follow me on LinkedIn](https://www.linkedin.com/in/chimeremeze-prevail-ejimadu-3a3535219)!

Also check out my page on medium to catch articles and tutorials on Laravel [follow me on medium](https://medium.com/@prevailexcellent)!

Thanks!
Chimeremeze Prevail Ejimadu.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
