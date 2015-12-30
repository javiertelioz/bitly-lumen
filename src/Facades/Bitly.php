<?php

/**
 * Bitly Library for Lumen PHP
 *
 * @package    bitly
 * @author     javier telio z <jtelio118@gmail.com>
 * @version    1.1
 *
 */
namespace Javiertelioz\BitlyLumen\Facades;

use Illuminate\Support\Facades\Facade;

class Bitly extends Facade
{
    protected static function getFacadeAccessor() {
        return 'bitly';
    }
}
