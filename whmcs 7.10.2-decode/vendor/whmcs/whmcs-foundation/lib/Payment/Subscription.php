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

class Subscription
{
    public static function getInfo($relatedItem)
    {
        try {
            $instanceType = get_class($relatedItem);
            if (!in_array($instanceType, array("WHMCS\\Service\\Addon", "WHMCS\\Domain\\Domain", "WHMCS\\Service\\Service"))) {
                throw new \InvalidArgumentException("Invalid Access Attempt");
            }
            $gatewayInterface = \WHMCS\Module\Gateway::factory($relatedItem->paymentGateway);
            if (!$gatewayInterface->functionExists("get_subscription_info") || !$relatedItem->subscriptionId) {
                throw new \InvalidArgumentException("Invalid Access Attempt");
            }
            $params = $gatewayInterface->getParams();
            $params["subscriptionId"] = $relatedItem->subscriptionId;
            $subscriptionInfo = $gatewayInterface->call("get_subscription_info", $params);
            if (!$subscriptionInfo) {
                throw new \WHMCS\Exception\Module\NotServicable("Invalid Response");
            }
            $subscriptionDetails = "";
            foreach ($subscriptionInfo as $key => $value) {
                $langId = "subscription." . str_replace("_", "", strtolower($key));
                $keyTranslation = \AdminLang::trans($langId);
                if (!$keyTranslation || $keyTranslation == $langId) {
                    $keyTranslation = $key;
                }
                $subscriptionDetails .= $keyTranslation . ": " . $value . "<br>";
            }
            $response = array("body" => view("admin.client.profile.subscription-info", array("isActive" => strtolower($subscriptionInfo["Status"]) == "active", "subscriptionDetails" => $subscriptionDetails)));
        } catch (\Exception $e) {
            $response = array("body" => view("admin.client.profile.subscription-info", array("errorMsg" => $e->getMessage())));
        }
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
    public static function cancel($relatedItem)
    {
        try {
            if ($relatedItem instanceof \WHMCS\Service\Addon) {
                $logMessage = " - Service Addon ID: " . $relatedItem->id;
            } else {
                if ($relatedItem instanceof \WHMCS\Domain\Domain) {
                    $logMessage = " - Domain ID: " . $relatedItem->id;
                } else {
                    if ($relatedItem instanceof \WHMCS\Service\Service) {
                        $logMessage = " - Service ID: " . $relatedItem->id;
                    } else {
                        throw new \InvalidArgumentException("Invalid Access Attempt");
                    }
                }
            }
            $paymentMethod = $relatedItem->paymentGateway;
            $subscriptionId = $relatedItem->subscriptionId;
            $gatewayInterface = \WHMCS\Module\Gateway::factory($paymentMethod);
            if (!$gatewayInterface->functionExists("cancelSubscription")) {
                throw new \WHMCS\Exception\Gateways\SubscriptionCancellationNotSupported("Subscription Cancellation not Support by Gateway");
            }
            $params = array("subscriptionID" => $subscriptionId);
            $cancelResult = $gatewayInterface->call("cancelSubscription", $params);
            if (is_array($cancelResult) && $cancelResult["status"] == "success") {
                $relatedItem->subscriptionId = "";
                $relatedItem->save();
                if ($relatedItem instanceof \WHMCS\Service\Addon) {
                    \WHMCS\Service\Addon::where("subscriptionid", $subscriptionId)->where("paymentmethod", $paymentMethod)->where("userid", $relatedItem->clientId)->update(array("subscriptionid" => ""));
                } else {
                    if ($relatedItem instanceof \WHMCS\Domain\Domain) {
                        \WHMCS\Domain\Domain::where("subscriptionid", $subscriptionId)->where("paymentmethod", $paymentMethod)->where("userid", $relatedItem->clientId)->update(array("subscriptionid" => ""));
                    } else {
                        if ($relatedItem instanceof \WHMCS\Service\Service) {
                            \WHMCS\Service\Service::where("subscriptionid", $subscriptionId)->where("paymentmethod", $paymentMethod)->where("userid", $relatedItem->clientId)->update(array("subscriptionid" => ""));
                        }
                    }
                }
                logActivity("Subscription Cancellation for ID " . $subscriptionId . " Successful" . $logMessage, $relatedItem->clientId);
                logTransaction($paymentMethod, $cancelResult["rawdata"], "Subscription Cancellation Success");
                $response = array("success" => true, "successMsgTitle" => \AdminLang::trans("global.success"), "successMsg" => \AdminLang::trans("services.cancelSubscriptionSuccess"));
            } else {
                logActivity("Subscription Cancellation for ID " . $subscriptionId . " Failed" . $logMessage, $relatedItem->clientId);
                logTransaction($paymentMethod, $cancelResult["rawdata"], "Subscription Cancellation Failed");
                $errorMsg = "Subscription Cancellation Failed";
                if (isset($cancelResult["errorMsg"])) {
                    $errorMsg .= ": " . $cancelResult["errorMsg"];
                }
                throw new \WHMCS\Exception\Gateways\SubscriptionCancellationFailed($errorMsg);
            }
        } catch (\Exception $e) {
            $response = array("errorMsg" => $e->getMessage(), "errorMsgTitle" => \AdminLang::trans("global.erroroccurred"));
        }
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
}

?>