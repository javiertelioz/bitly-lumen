<?php

/**
 * Bitly Library for Lumen PHP
 *
 * @package    bitly
 * @author     javier telio z <jtelio118@gmail.com>
 * @version    1.1
 *
 */
namespace Javiertelioz\BitlyLumen;

use Javiertelioz\BitlyLumen\Bitly;
use Illuminate\Support\ServiceProvider;

class BitlyLumenServiceProvider extends ServiceProvider
{
    protected $username;
    protected $password;
    protected $client_id;
    protected $client_secret;
    protected $method;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        // Load Config
        $this->username = env('BITLY_LOGIN');
        $this->password = env('BITLY_KEY');
        $this->client_id = env('BITLY_CLIENT_ID');
        $this->client_secret = env('BITLY_CLIENT_SECRET');
        $this->method = env('BITLY_METHOD');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {

        // Register Facade
        $this->app->singleton('bitly', function () {
            return new Bitly($this->username, $this->password, $this->client_id, $this->client_secret);
        });
    }
}
