<?php

/*
 * This file is part of the Laravel Bani package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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

    /**
     * Your callback URL
     *
     */
    'callbackUrl' => getenv('BANI_CALLBACK_URL'),

];