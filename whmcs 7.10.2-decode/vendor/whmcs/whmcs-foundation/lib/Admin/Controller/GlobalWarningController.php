<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Controller;

class GlobalWarningController
{
    public function dismiss(\WHMCS\Http\Message\ServerRequest $request)
    {
        $alertToDismiss = $request->get("alert");
        (new \WHMCS\Admin\ApplicationSupport\View\Html\Helper\GlobalWarning())->updateDismissalTracker($alertToDismiss);
        return new \WHMCS\Http\Message\JsonResponse(array("status" => "success"));
    }
}

?>