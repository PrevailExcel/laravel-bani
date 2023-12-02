<?php

namespace PrevailExcel\Bani;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Nette\Utils\Random;

/*
 * This file is part of the Laravel Bani package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Bani
{
    use Customer, Collection, Verification, Misc;


    /**
     * Issue Merchant Key from your Bani Dashboard
     * @var string
     */
    protected $merchantKey;

    /**
     * Issue Public Key from your Bani Dashboard
     * @var string
     */
    protected $publicKey;

    /**
     * Issue Tribe Account Ref Key from your Bani Dashboard
     * @var string
     */
    protected $tribeAccRef;

    /**
     * Access Token from your Bani Dashboard
     * @var string
     */
    protected $accessToken;

    /**
     * Instance of Client
     * @var Client
     */
    protected $client;

    /**
     *  Response from requests made to Bani
     * @var mixed
     */
    protected $response;

    /**
     * Bani API base Url
     * @var string
     */
    protected $baseUrl;

    /**
     * Bani API Enviroment
     * @var string
     */
    protected $env;

    /**
     * Verified Data from Webhook
     */
    protected $webhookData;

    /**
     * Your callback Url. You can set this in your .env or you can add 
     * it to the $data in the methods that require that option.
     * @var string
     */
    protected $callbackUrl;

    public function __construct()
    {
        $this->authorize();
        $this->setBaseUrl();
        $this->setRequestOptions();
    }

    /**
     * Set properties from Bani config file
     */
    private function authorize()
    {
        $this->tribeAccRef = Config::get('bani.tribeAccountRef');
        $this->publicKey = Config::get('bani.publicKey');
        $this->merchantKey = Config::get('bani.merchantKey');
        $this->accessToken = Config::get('bani.accessToken');
        $this->env = Config::get('bani.env');
        $this->callbackUrl = Config::get('bani.callbackUrl');
    }

    /**
     * Get Base Url from NOWPayment config file
     */
    private function setBaseUrl()
    {
        if ($this->env == "stage")
            $this->baseUrl = Config::get('bani.stageUrl');
        else
            $this->baseUrl = Config::get('bani.liveUrl');
    }

    /**
     * The value of MONI-SIGNATURE will be a combination of the 
     * tribe_account_ref, public_key, and merchant_private_key. 
     * This is to verify the source sending the request.
     */
    private function generateSignature()
    {
        $digest = $this->tribeAccRef . $this->publicKey;
        $signature = hash_hmac('sha256', $digest, $this->merchantKey);
        return $signature;
    }

    /**
     * Set options for making the Client request
     */
    private function setRequestOptions()
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'moni-signature' => $this->generateSignature(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json'
        ];
        $this->client = new Client(
            [
                'base_uri' => $this->baseUrl,
                'headers' => $headers
            ]
        );
    }

    /**
     * @param string $relativeUrl
     * @param string $method
     * @param array $body
     * @return Bani
     * @throws IsNullException
     */
    private function setHttpResponse($relativeUrl, $method, $body = [])
    {
        if (is_null($method)) {
            throw new IsNullException("Empty method not allowed");
        }

        $this->response = $this->client->{strtolower($method)}(
            $this->baseUrl . $relativeUrl,
            ["body" => json_encode($body)]
        );

        return $this;
    }

    /**
     * Get the whole response from a get operation
     * @return array
     */
    private function getResponse()
    {
        return json_decode($this->response->getBody(), true);
    }
    
    /**
     * Get payment widget for payment
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function payWithWidget($data = null)
    {
        if ($data == null) {
            $data = [
                "amount" => request()->amount,
                "phoneNumber" => request()->phone,
                "email" => request()->email,
                "firstName" => request()->firstName,
                "lastName" => request()->lastName,
                "metadata" => request()->metadata,
                "merchantRef" => Random::generate(),
                "merchantKey" => $this->publicKey,
                "country_code" => "NG"
            ];
        }

        return view('bani::bani', compact('data'));
    }
    
    /**
     * Get payment data
     * @return array
     */
    public function getPaymentData()
    {
        $ref = request()->paymentRef;
        $type = request()->paymentType;
        $paymentdata = $this->verifyTransaction($ref, $type);
        return $paymentdata;
    }

    /**
     * Verify Transaction with reference
     * 
     * @param string $ref
     * @param string $type
     * @return array
     */
    public function verifyTransaction($ref, $type)
    {
        $data = array_filter([
            "pay_ref" => $ref
        ]);

        // FOR FIAT OR CRYPTO
        if ($type == "fiat")
            dd($this->setHttpResponse('/partner/collection/pay_status_check/', 'POST', $data)->getResponse());
        else
            return $this->setHttpResponse('/partner/collection/coin_payment_status/' . $ref, 'GET')->getResponse();
    }    
    
    /**
     * Verify webhook data
     * 
     * @return Bani
     * @throws IsNullException
     */
    public function getWebhookData()
    {
        $computedSignature = hash_hmac('sha256', request()->getContent(), $this->merchantKey);
        $signature = request()->header('bani-hook-signature');
        $verified = hash_equals($signature, $computedSignature);
        if ($verified) {
            $this->webhookData = request()->getContent();
            return $this;
        } else
            throw IsNullException::make();
    }

    /**
     * Handle webhook data
     * @return array
     */
    public function proccessData(callable|Closure $callback)
    {    
        call_user_func($callback, $this->webhookData);
        return true;
    }
}
