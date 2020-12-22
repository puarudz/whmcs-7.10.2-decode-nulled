<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\AcceptJs;

class AcceptJsAuthenticateTestController extends \net\authorize\api\controller\AuthenticateTestController
{
    public function __construct(\net\authorize\api\contract\v1\AnetApiRequestType $request)
    {
        parent::__construct($request);
        $this->httpClient = new AcceptJsHttpClient();
    }
}

?>