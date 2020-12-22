<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\ApplicationLink\Server;

interface ApplicationLinkServerInterface extends \OAuth2\Controller\TokenControllerInterface, \OAuth2\Controller\ResourceControllerInterface
{
    public function postAccessTokenResponseAction(\OAuth2\RequestInterface $request, \OAuth2\ResponseInterface $response);
}

?>