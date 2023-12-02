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

trait Verification
{
    /**
     * The fastest and most secure way to verify mobile numbers using USSD instead of SMS OTPs.
     * 
     * @param array $data
     * @return array
     */

    public function verifyPhone(?string $phone): array
    {
        if (!$phone)
            $phone = request()->phone;

        $data = array_filter([
            "holder_phone" => $phone
        ]);

        return $this->setHttpResponse('/partner/verification/phone_ussd/', 'POST', $data)->getResponse();
    }

    /**
     * This endpoint is used to confirmed if the customer phone number has been verified
     * 
     * @param array $data
     * @return array
     */

    public function checkUssdStatus(?string $verification_reference): array
    {
        if (!$verification_reference)
            $verification_reference = request()->verification_reference;

        return $this->setHttpResponse('/partner/verification/phone_ussd/'. $verification_reference , 'GET')->getResponse();
    }
}
