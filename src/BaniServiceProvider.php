<?php

namespace PrevailExcel\Bani;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/*
 * This file is part of the Laravel Bani package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class BaniServiceProvider extends ServiceProvider
{

    /*
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    protected $defer = false;

    /**
     * Publishes all the config file this package needs to function
     */
    public function boot()
    {
        $config = realpath(__DIR__ . '/../utils/config/bani.php');

        $this->publishes([
            $config => config_path('bani.php')
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/../utils/config/bani.php',
            'bani'
        );
        if (File::exists(__DIR__ . '/../utils/helpers/bani.php')) {
            require __DIR__ . '/../utils/helpers/bani.php';
        }

        $this->loadViewsFrom(__DIR__ . '/../views/', 'bani');

        /**
         * @param  array|string $controller
         * @param  string|null  $class
         * */
        Route::macro('callback', function ($controller, string $class = 'handleGatewayCallback') {
            return Route::any('bani/callback', [$controller, $class])->name("bani.lara.callback");
        });
        Route::macro('webhook', function ($controller, string $class = 'handleWebhook') {
            return Route::post('bani/webhook', [$controller, $class])->name("bani.lara.webhook");
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('laravel-bani', function () {
            return new Bani;
        });
    }

    /**
     * Get the services provided by the provider
     * @return array
     */
    public function provides()
    {
        return ['laravel-bani'];
    }
}
