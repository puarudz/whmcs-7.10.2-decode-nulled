<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Application\Support\ServiceProvider;

abstract class AbstractServiceProvider
{
    protected $app = NULL;
    public function __construct(\WHMCS\Container $app)
    {
        $this->app = $app;
    }
    public abstract function register();
}

?>