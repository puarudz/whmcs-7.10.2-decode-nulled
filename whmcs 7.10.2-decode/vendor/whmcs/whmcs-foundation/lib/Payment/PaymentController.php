<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment;

class PaymentController
{
    public function confirm(\WHMCS\Http\Message\ServerRequest $request)
    {
        $gatewayName = $request->get("gateway");
        $gateway = new \WHMCS\Module\Gateway();
        if (!$gatewayName) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Invalid Request"));
        }
        if (!$gateway->load($gatewayName)) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Module Not Active"));
        }
        $remoteStorageToken = \WHMCS\Session::getAndDelete($gatewayName . "Confirm");
        if (!$remoteStorageToken) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Invalid Request"));
        }
        if (!$gateway->functionExists("remote_input_confirm")) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Unsupported Request"));
        }
        $result = $gateway->call("remote_input_confirm", array("gatewayid" => $remoteStorageToken, "remoteStorageToken" => $remoteStorageToken));
        if (array_key_exists("warning", $result) && $result["warning"]) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => $result["warning"]));
        }
        $client = \WHMCS\User\Client::find(\WHMCS\Session::get("uid"));
        $payMethod = PayMethod\Adapter\RemoteCreditCard::factoryPayMethod($client, $client->billingContact);
        $payment = $payMethod->payment;
        $payMethod->setGateway($gateway);
        $payment->setCardNumber($result["cardnumber"])->setExpiryDate(\WHMCS\Carbon::createFromCcInput($result["cardexpiry"]))->setRemoteToken($result["gatewayid"])->save();
        $payMethod->save();
        \WHMCS\Session::set("payMethodCreateSuccess", true);
        return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "redirect" => $result["redirect"]));
    }
    public function update(\WHMCS\Http\Message\ServerRequest $request)
    {
        $gatewayName = $request->get("gateway");
        $payMethodId = $request->get("pay_method_id");
        $gateway = new \WHMCS\Module\Gateway();
        if (!$gatewayName) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Invalid Request"));
        }
        if (!$gateway->load($gatewayName)) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Module Not Active"));
        }
        $remoteStorageToken = \WHMCS\Session::getAndDelete($gatewayName . "Confirm");
        if (!$remoteStorageToken || !$payMethodId) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Invalid Request"));
        }
        $payMethod = PayMethod\Model::find($payMethodId);
        if ($payMethod->gateway_name != $gatewayName) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Invalid Request"));
        }
        if (!$gateway->functionExists("remote_input_confirm")) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Unsupported Request"));
        }
        $result = $gateway->call("remote_input_confirm", array("gatewayid" => $remoteStorageToken, "remoteStorageToken" => $remoteStorageToken));
        if (array_key_exists("warning", $result) && $result["warning"]) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => $result["warning"]));
        }
        $payment = $payMethod->payment;
        $payMethod->setGateway($gateway);
        $payment->setCardNumber($result["cardnumber"])->setExpiryDate(\WHMCS\Carbon::createFromCcInput($result["cardexpiry"]))->setRemoteToken($result["gatewayid"])->save();
        $payMethod->save();
        \WHMCS\Session::set("payMethodSaveSuccess", true);
        return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "redirect" => ""));
    }
    public function getRemoteToken(\WHMCS\Http\Message\ServerRequest $request)
    {
        $paymentModule = $request->get("module");
        try {
            $gateway = \WHMCS\Module\Gateway::factory($paymentModule);
            $payMethodId = $request->get("paymethod_id", 0);
            if (!$payMethodId) {
                throw new \InvalidArgumentException("Invalid Request: Missing Pay Method ID");
            }
            $payMethod = PayMethod\Model::find($payMethodId);
            if (!$payMethod) {
                throw new \InvalidArgumentException("Invalid Request: Invalid Payment ID");
            }
            $userId = (int) \WHMCS\Session::get("uid");
            if (!$userId || (int) $payMethod->userid !== $userId) {
                throw new \WHMCS\Exception("Invalid Access Attempt");
            }
            if ($payMethod->gateway_name != $paymentModule) {
                throw new \InvalidArgumentException("Invalid PayMethod for Gateway");
            }
            if (!$payMethod->payment instanceof Contracts\RemoteTokenDetailsInterface) {
                throw new \InvalidArgumentException("Invalid PayMethod for Gateway");
            }
            $remoteToken = $payMethod->payment->getRemoteToken();
            if ($gateway->functionExists("get_existing_remote_token")) {
                $params = $gateway->getParams();
                $params["gatewayid"] = $remoteToken;
                $params["remoteToken"] = $remoteToken;
                $params["payMethod"] = $payMethod;
                $remoteToken = $gateway->call("get_existing_remote_token", $params);
            }
            if (!$remoteToken) {
                throw new \InvalidArgumentException("Invalid PayMethod Data for Gateway");
            }
            return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "token" => $remoteToken));
        } catch (\WHMCS\Exception $e) {
            $message = \Lang::trans("errors.badRequestTryAgain");
        } catch (\Exception $e) {
            $message = \Lang::trans("errors.badRequest");
        }
        return new \WHMCS\Http\Message\JsonResponse(array("warning" => $message));
    }
}

?>