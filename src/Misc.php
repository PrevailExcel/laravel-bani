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

trait Misc
{
    /**
     * This endpoint can be used to carry out a lookup on customer account
     * @param mixed $wallet
     * @param mixed $tag - This can username, email or phone number
     * @param null|string $type
     * @return array
     */

    public function confirmEwallet($wallet, $tag, ?string $type = null): array
    {
        if (!$wallet)
            $wallet = request()->$wallet ?? null;
        if (!$tag)
            $tag = request()->tag ?? request()->username ?? request()->phone ?? null;
        if (!$type)
            $type = request()->$type ?? null;

        $data = array_filter([
            "wallet_name" => $wallet,
            "verify_tag" => $tag,
            "verify_type" => $type
        ]);

        return $this->setHttpResponse('/partner/info/ewallet/', 'POST', $data)->getResponse();
    }
}
