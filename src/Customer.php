<?php

namespace PrevailExcel\Bani;

/*
 * This file is part of the Laravel Bani package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait Customer
{
    /**
     * Check if the customer already exist
     * For this, you either pass the customer_phone or customer_ref
     * @return array
     * @param string|null $phone 
     * @param string|null $ref
     * @throws isNullException
     */
    public function customer(string $phone = null, string $ref = null): array
    {
        if (!$phone)
            $phone = request()->phone ?? null;
        if (!$ref)
            $ref = request()->ref ?? null;

        $customer = array_filter([
            "customer_phone" => $phone,
            "customer_ref" => $ref
        ]);
        return $this->setHttpResponse("/comhub/check_customer/", 'POST', $customer)->getResponse();
    }

    /**
     * Create a customer object
     * 
     * @param array $data
     * @return array
     */

    public function createCustomer($data = null): array
    {
        if ($data == null) {
            $data = [
                "customer_first_name" => request()->first_name,
                "customer_last_name" => request()->last_name,
                "customer_phone" => request()->phone,
                "customer_email" => request()->email,
                "customer_address" => request()->address,
                "customer_state" => request()->state,
                "customer_city" => request()->city,
            ];
        }

        return $this->setHttpResponse('/comhub/add_my_customer/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * update a customer object
     * 
     * @param array $data
     * @return array
     */

    public function updateCustomer($data = null): array
    {
        if ($data == null) {
            $data = [
                "customer_ref" => request()->ref,
                "customer_first_name" => request()->first_name,
                "customer_last_name" => request()->last_name,
                "customer_phone" => request()->phone,
                "customer_email" => request()->email,
                "customer_address" => request()->address,
                "customer_state" => request()->state,
                "customer_city" => request()->city,
            ];
        }

        return $this->setHttpResponse('/comhub/edit_my_customer/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * With this endpoint, you can fetch your customer delivery/billing address(es).
     * NG is the default delivery country code
     * @return array
     * @param string|null $ref
     * @param string|null $country_code
     * @throws isNullException
     */
    public function listBillingAddress(string $ref = null, ?string $country_code): array
    {
        if (!$country_code)
            $country_code = request()->country_code ?? "NG";
        if (!$ref)
            $ref = request()->ref ?? null;

        $data = array_filter([
            "delivery_country_code" => $country_code,
            "customer_ref" => $ref
        ]);
        return $this->setHttpResponse("/comhub/list_customer_billing/", 'POST', $data)->getResponse();
    }

    /**
     * This endpoint can be used to add customer delivery/billing address(es) 
     * 
     * @param array $data
     * @return array
     */

    public function addBillingAddress($data = null): array
    {
        if ($data == null) {
            $data = [
                "delivery_country" => request()->country_code,
                "customer_ref" => request()->ref,
                "delivery_address" => request()->address,
                "delivery_city" => request()->city,
                "delivery_state" => request()->state,
                "delivery_zip_code" => request()->zip,
                "delivery_note" => request()->note
            ];
        }

        return $this->setHttpResponse('/comhub/add_customer_billing/', 'POST', array_filter($data))->getResponse();
    }
    
    /**
     * This endpoint can be used to update customer's delivery/billing address
     * 
     * @param array $data
     * @return array
     */

    public function updateBillingAddress($data = null): array
    {
        if ($data == null) {
            $data = [
                "delivery_id" => request()->id, //required
                "customer_ref" => request()->ref, //required
                "delivery_country" => request()->country_code,
                "delivery_address" => request()->address,
                "delivery_city" => request()->city,
                "delivery_state" => request()->state,
                "delivery_zip_code" => request()->zip,
                "delivery_note" => request()->note
            ];
        }

        return $this->setHttpResponse('/comhub/edit_customer_billing/', 'POST', array_filter($data))->getResponse();
    }

    /**
     * This endpoint can be used to delete customer's delivery/billing address
     * 
     * @param array $data
     * @return array
     */

    public function deleteBillingAddress(?string $ref, ?string $id): array
    {
        if (!$id)
            $id = request()->id;
        if (!$ref)
            $ref = request()->ref;

        $data = array_filter([
            "delivery_id" => $id,
            "customer_ref" => $ref,
            "is_delete" => true
        ]);

        return $this->setHttpResponse('/comhub/edit_customer_billing/', 'POST', $data)->getResponse();
    }
}
