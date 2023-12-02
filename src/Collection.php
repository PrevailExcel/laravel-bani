<?php

namespace PrevailExcel\Bani;

use Nette\Utils\Random;

/*
 * This file is part of the Laravel Bani package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait Collection
{
    /**
     * Accept payments from customers via bank transfer
     * 
     * @param array $data
     * @return array
     */

    public function bankTransfer($data = null): array
    {
        $def = [
            "pay_va_step" => "direct",
            "country_code" => "NG",
            "pay_currency" => "NGN",
            "pay_ext_ref" => Random::generate(),
        ];

        if ($data == null) {
            $data = [
                "pay_amount" => request()->amount,
                "holder_account_type" => request()->account_type ?? "temporary",
                "customer_ref" => request()->ref,
                "custom_data" => request()->custom_data ?? []
            ];
        }
        $data = array_merge($def, $data);

        return $this->setHttpResponse('/partner/collection/bank_transfer/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Accept payments via mobile money from customers
     * 
     * @param array $data
     * @return array
     */

    public function mobileMoney($data = null): array
    {
        $def = [
            "pay_va_step" => "direct",
            "country_code" => "NG",
            "pay_currency" => "NGN",
            "pay_ext_ref" => Random::generate(),
        ];

        if ($data == null) {
            $data = [
                "holder_phone" => request()->phone,
                "holder_ip_address" => request()->ip_address,
                "holder_device_tag" => request()->device_tag,
                "pay_amount" => request()->amount,
                "customer_ref" => request()->ref,
                "custom_data" => request()->custom_data ?? []
            ];
        }
        $data = array_merge($def, $data);

        return $this->setHttpResponse('/partner/collection/mobile_money/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Accept payments from customers via crypto and get settled in fiat
     * 
     * @param array $data
     * @return array
     */

    public function payWithCrypto($data = null): array
    {
        if ($data == null) {
            $data = [
                "coin_type" => request()->coin,
                "fiat_deposit_amount" => request()->fiat_amount,
                "fiat_deposit_currency" => request()->fiat_currency ?? "NGN",
                "coin_deposit_amount" => request()->coin_amount,
                "customer_ref" => request()->ref,
                "coin_deposit_ref" => Random::generate() . rand(11111, 99999),
                "coin_chain_network" => request()->chain_network,
                "custom_data" => request()->custom_data ?? []
            ];
        }

        return $this->setHttpResponse('/partner/collection/crypto/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * This endpoint can be used to generate a deposit address to accept 
     * strictly DLT payments and get settled with the same currency. 
     * No fiat conversion.
     * 
     * @param array $data
     * @return array
     */

    public function getWalletAddress($data = null): array
    {
        if ($data == null) {
            $data = [
                "coin_type" => request()->coin,
                "customer_ref" => request()->ref,
                "coin_deposit_ref" => Random::generate() . rand(11111, 99999),
                "coin_chain_network" => request()->chain_network,
                "custom_data" => request()->custom_data ?? []
            ];
        }

        return $this->setHttpResponse('/partner/collection/fund_with_crypto/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Accept one-click payment from customers via bani shopper wallet
     * 
     * @param array $data
     * @return array
     */

    public function startPayWithBaniShopper($data = null): array
    {
        $def = [
            "estep" => "initialize",
            "ewallet_name" => "bani",
            "country_code" => "NG",
            "pay_currency" => "NGN",
            "pay_ext_ref" => Random::generate(),
        ];

        if ($data == null) {
            $data = [
                "customer_ref" => request()->ref,
                "custom_data" => request()->custom_data ?? [],
                "pay_amount" => request()->amount,
                "account_identifier" => request()->account_identifier

            ];
        }

        $data = array_merge($def, $data);

        // Check username first 
        $check = $this->confirmEwallet("bani", $data["account_identifier"] ?? request()->account_identifier);
        if ($check["status"])
            return $this->setHttpResponse('/partner/collection/ewallet/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Complete 2nd step in payment from customers via bani shopper wallet
     * 
     * @param array $data
     * @return array
     */

    public function completePayWithBaniShopper($data = null): array
    {
        $def = [
            "estep" => "finalize_payment",
            "ewallet_name" => "bani",
        ];

        if ($data == null) {
            $data = [
                "eotp" => request()->eotp,
                "epass_ref" => request()->epass_ref
            ];
        }

        $data = array_merge($def, $data);
        return $this->setHttpResponse('/partner/collection/ewallet/', 'POST', array_filter($data))->getResponse();
    }
    
    /**
     * Accept one-click payment from customers via opay
     * 
     * @param array $data
     * @return array
    */

     public function startPayWithOpay($data = null): array
     {
         $def = [
             "estep" => "initialize",
             "ewallet_name" => "opay",
             "country_code" => "NG",
             "pay_currency" => "NGN",
             "holder_ip_address" => request()->ip(),
             "pay_ext_ref" => Random::generate(),
         ];
 
         if ($data == null) {
             $data = [
                "customer_ref" => request()->ref,
                "custom_data" => request()->custom_data ?? [],
                "pay_amount" => request()->amount,
                "account_type" => request()->account_type,                 
                "holder_phone" => request()->phone,
                "epin" => request()->epin,
                "holder_first_name" => request()->firstName,
                "holder_last_name" => request()->lastName
             ];
         }
 
         $data = array_merge($def, $data);
 
         // Check username first 
         $check = $this->confirmEwallet("opay", $data["holder_phone"], $data["account_type"] ?? request()->account_type );
         if ($check["status"])
             return $this->setHttpResponse('/partner/collection/ewallet/', 'POST', array_filter($data))->getResponse();
     }

     /**
      * Send OTP to Opay
      * 
      * @param array $data
      * @return array
      */
 
      public function sendOtpToOpay(?string $epass_ref): array
      {
        $def = [
            "estep" => "send_otp",
            "ewallet_name" => "opay"
        ];

        $data = [
            "epass_ref" => $epass_ref ?? request()->epass_ref,
        ];
        
        $data = array_merge($def, $data);
  
        return $this->setHttpResponse('/partner/collection/ewallet/', 'POST', array_filter($data))->getResponse();
      }

    /**
     * Complete 2nd step in payment from customers via Opay
     * 
     * @param array $data
     * @return array
     */

    public function completePayWithOpay($data = null): array
    {
        $def = [
            "estep" => "finalize_payment",
            "ewallet_name" => "opay",
        ];

        if ($data == null) {
            $data = [
                "eotp" => request()->eotp,
                "epass_ref" => request()->epass_ref
            ];
        }

        $data = array_merge($def, $data);
        return $this->setHttpResponse('/partner/collection/ewallet/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Accept one-click payment from customers via bani shopper wallet
     * 
     * @param array $data
     * @return array
     */

    public function startPayWithPocketApp($data = null): array
    {
        $def = [
            "estep" => "initialize",
            "ewallet_name" => "pocketapp",
            "country_code" => "NG",
            "pay_currency" => "NGN",
            "pay_ext_ref" => Random::generate(),
        ];

        if ($data == null) {
            $data = [
                "customer_ref" => request()->ref,
                "custom_data" => request()->custom_data ?? [],
                "pay_amount" => request()->amount,
                "account_identifier" => request()->account_identifier

            ];
        }

        $data = array_merge($def, $data);

        // Check username first 
        $check = $this->confirmEwallet("pocketapp", $data["account_identifier"] ?? request()->account_identifier);
        if ($check["status"])
            return $this->setHttpResponse('/partner/collection/ewallet/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Complete 2nd step in payment from customers via pocketapp shopper wallet
     * 
     * @param array $data
     * @return array
     */

    public function completePayWithPocketApp($data = null): array
    {
        $def = [
            "estep" => "finalize_payment",
            "ewallet_name" => "pocketapp",
        ];

        if ($data == null) {
            $data = [
                "eotp" => request()->eotp,
                "epass_ref" => request()->epass_ref
            ];
        }

        $data = array_merge($def, $data);
        return $this->setHttpResponse('/partner/collection/ewallet/', 'POST', array_filter($data))->getResponse();
    }
    
 
}
