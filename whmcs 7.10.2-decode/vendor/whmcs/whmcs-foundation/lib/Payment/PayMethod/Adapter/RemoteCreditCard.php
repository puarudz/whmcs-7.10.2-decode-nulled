<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\PayMethod\Adapter;

class RemoteCreditCard extends CreditCardModel implements \WHMCS\Payment\Contracts\RemoteTokenDetailsInterface
{
    use \WHMCS\Payment\PayMethod\Traits\CreditCardDetailsTrait;
    public static function boot()
    {
        parent::boot();
        static::saving(function (RemoteCreditCard $model) {
            $model->unsetSensitiveProperty("cardNumber");
            $sanitizedSensitiveData = $model->getSensitiveData();
            $name = $model->getSensitiveDataAttributeName();
            $model->{$name} = $sanitizedSensitiveData;
        });
    }
    public function getRemoteToken()
    {
        $remoteToken = $this->getSensitiveProperty("remoteToken");
        if (is_array($remoteToken)) {
            $remoteToken = json_encode($remoteToken);
        }
        if (!is_string($remoteToken)) {
            $remoteToken = (string) $remoteToken;
        }
        return $remoteToken;
    }
    public function setRemoteToken($value)
    {
        $this->setSensitiveProperty("remoteToken", $value);
        return $this;
    }
    public function getPaymentParamsForRemoteCall()
    {
        $expiryDate = $this->getExpiryDate();
        return array("cardtype" => $this->getCardType(), "cardnum" => $this->getCardNumber(), "cardcvv" => $this->getCardCvv(), "cardexp" => $expiryDate ? $expiryDate->format("my") : 0, "cardExpiryMonth" => $expiryDate ? $expiryDate->format("m") : 0, "cardExpiryYear" => $expiryDate ? $expiryDate->format("Y") : 0, "cardstart" => $this->getStartDate() ? $this->getStartDate()->format("my") : 0, "cardissuenum" => $this->getIssueNumber(), "cardlastfour" => $this->getLastFour());
    }
    protected function getGatewayParamsForRemoteCall(\WHMCS\Module\Gateway $gateway)
    {
        $params = $gateway->loadSettings();
        if (!$params) {
            throw new \WHMCS\Exception\Module\InvalidConfiguration("No Gateway Settings Found");
        }
        if (!empty($params["convertto"])) {
            $currencyCode = \WHMCS\Database\Capsule::table("tblcurrencies")->where("id", (int) $params["convertto"])->value("code");
            $params["currency"] = $currencyCode;
        }
        if (empty($params["currency"])) {
            $clientCurrency = $this->payMethod->client->currencyrel->code;
            $params["currency"] = $clientCurrency;
        }
        $params["companyname"] = \WHMCS\Config\Setting::getValue("CompanyName");
        $params["systemurl"] = \App::getSystemURL();
        $params["langpaynow"] = \Lang::trans("invoicespaynow");
        return $params;
    }
    public function getBillingContactParamsForRemoteCall(\WHMCS\User\Contracts\UserInterface $client, \WHMCS\User\Contracts\ContactInterface $contact = NULL)
    {
        $client = new \WHMCS\Client($client->id);
        $clientsDetails = $client->getDetails($contact ? $contact->id : "billing");
        $clientsDetails["state"] = $clientsDetails["statecode"];
        return array("clientdetails" => $clientsDetails);
    }
    protected function storeRemote($action)
    {
        $payMethod = $this->payMethod;
        $gateway = $payMethod->getGateway();
        if (!$gateway) {
            throw new \RuntimeException("No valid gateway for PayMethod ID " . $this->payMethod->id);
        }
        $paymentParams = $this->getPaymentParamsForRemoteCall();
        $gatewayParams = $this->getGatewayParamsForRemoteCall($gateway);
        $billingParams = $this->getBillingContactParamsForRemoteCall($payMethod->client, $payMethod->contact);
        $params = array_merge($gatewayParams, $paymentParams, $billingParams);
        $params["action"] = $action;
        $params["payMethod"] = $payMethod;
        $params["gatewayid"] = $this->getRemoteToken();
        $params["remoteStorageToken"] = $this->getRemoteToken();
        if ($gateway->functionExists("storeremote")) {
            $gatewayCallResult = $gateway->call("storeremote", $params);
            if (is_array($gatewayCallResult["rawdata"])) {
                $debugData = array_merge(array("UserID" => $params["clientdetails"]["userid"]), $gatewayCallResult["rawdata"]);
            } else {
                $debugData = "UserID => " . $params["clientdetails"]["userid"] . "\n" . $gatewayCallResult["rawdata"];
            }
            if ($gatewayCallResult["status"] == "success") {
                logTransaction($gateway->getLoadedModule(), $debugData, "Remote Storage Success");
                if (is_array($gatewayCallResult) && array_key_exists("gatewayid", $gatewayCallResult) && !array_key_exists("remoteToken", $gatewayCallResult)) {
                    $gatewayCallResult["remoteToken"] = $gatewayCallResult["gatewayid"];
                }
                if (isset($gatewayCallResult["remoteToken"])) {
                    $this->setRemoteToken($gatewayCallResult["remoteToken"]);
                    if (array_key_exists("cardtype", $gatewayCallResult)) {
                        $this->setCardType($gatewayCallResult["cardtype"]);
                    }
                    if (array_key_exists("cardlastfour", $gatewayCallResult)) {
                        $this->setLastFour($gatewayCallResult["cardlastfour"]);
                    }
                    if (array_key_exists("cardexpiry", $gatewayCallResult)) {
                        $expiry = $gatewayCallResult["cardexpiry"];
                        if (!$expiry instanceof \WHMCS\Carbon) {
                            try {
                                $expiry = \WHMCS\Carbon::createFromCcInput($expiry);
                            } catch (\Exception $e) {
                                $expiry = \WHMCS\Carbon::today()->endOfMonth()->endOfDay();
                            }
                        }
                        $this->setExpiryDate($expiry);
                    }
                    $this->runCcUpdateHook();
                } else {
                    if ($action == "create") {
                        logTransaction($gateway->getLoadedModule(), $debugData, "Remote Storage \"create\" action did NOT provide token");
                        throw new \RuntimeException("Remote Storage Failed");
                    }
                }
            } else {
                logTransaction($gateway->getLoadedModule(), $debugData, "Remote Storage Failed");
                throw new \RuntimeException("Remote Storage Failed");
            }
        }
        $this->save();
        return $this;
    }
    public function createRemote()
    {
        return $this->storeRemote("create");
    }
    public function updateRemote()
    {
        return $this->storeRemote("update");
    }
    public function deleteRemote()
    {
        return $this->storeRemote("delete");
    }
    public function getDisplayName()
    {
        return implode("-", array($this->card_type, $this->last_four));
    }
    public function validateRequiredValuesPreSave()
    {
        return $this;
    }
    public function validateRequiredValuesForEditPreSave()
    {
        return $this;
    }
}

?>