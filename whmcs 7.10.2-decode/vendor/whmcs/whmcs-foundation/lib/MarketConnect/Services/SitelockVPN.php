<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\MarketConnect\Services;

class SitelockVPN extends AbstractService
{
    public function provision($model, array $params = NULL)
    {
        $this->configure($model, $params);
    }
    public function configure($model, array $params = NULL)
    {
        $serviceProperties = $model->serviceProperties;
        $orderNumber = $serviceProperties->get("Order Number");
        if (!$orderNumber) {
            throw new \WHMCS\Exception\Module\NotServicable("You must provision this service before attempting to configure it");
        }
        $parentModel = null;
        $emailRelatedId = $model->id;
        if ($model instanceof \WHMCS\Service\Addon) {
            $parentModel = $model->service;
            $emailRelatedId = $parentModel->id;
        }
        $configure = array("order_number" => $orderNumber, "customer_name" => $model->client->fullName, "customer_email" => $model->client->email);
        $api = new \WHMCS\MarketConnect\Api();
        $api->configure($configure);
        $emailTemplate = "SiteLock VPN Welcome Email";
        if ($model instanceof \WHMCS\Service\Addon && $model->productAddon->welcomeEmailTemplateId) {
            $emailTemplate = $model->productAddon->welcomeEmailTemplate->name;
        } else {
            if ($model instanceof \WHMCS\Service\Service && $model->product->welcomeEmailTemplateId) {
                $emailTemplate = $model->product->welcomeEmailTemplate->name;
            }
        }
        sendMessage($emailTemplate, $emailRelatedId);
    }
    public function cancel($model, array $params = NULL)
    {
        $serviceProperties = $model->serviceProperties;
        $orderNumber = $serviceProperties->get("Order Number");
        if (!$orderNumber) {
            throw new \WHMCS\Exception\Module\NotServicable("You must provision this service before attempting to manage it");
        }
        $api = new \WHMCS\MarketConnect\Api();
        $response = $api->cancel($orderNumber);
        if (array_key_exists("error", $response)) {
            throw new \WHMCS\Exception($response["error"]);
        }
    }
    public function clientAreaAllowedFunctions($params)
    {
        if ($params["status"] != "Active") {
            return array();
        }
        return array("manage_order");
    }
    public function clientAreaOutput(array $params)
    {
        $orderNumber = marketconnect_GetOrderNumber($params);
        if (!$orderNumber) {
            return "";
        }
        $serviceId = $params["serviceid"];
        $addonId = array_key_exists("addonId", $params) ? $params["addonId"] : 0;
        $manageText = \Lang::trans("store.sitelockvpn.manageService");
        $webRoot = \WHMCS\Utility\Environment\WebHelper::getBaseUrl();
        return "<img src=\"" . $webRoot . "/assets/img/marketconnect/sitelock/logo.png\" style=\"max-width:300px;\">\n<br><br>\n<form style=\"display:inline;\">\n    <div class=\"login-feedback alert alert-warning hidden\"></div>\n    <input type=\"hidden\" name=\"modop\" value=\"custom\" />\n    <input type=\"hidden\" name=\"a\" value=\"manage_order\" />\n    <input type=\"hidden\" name=\"id\" value=\"" . $serviceId . "\" />\n    <input type=\"hidden\" name=\"addonId\" value=\"" . $addonId . "\" />\n    <button class=\"btn btn-default btn-service-sso\">\n        <span class=\"loading hidden\">\n            <i class=\"fas fa-spinner fa-spin\"></i>\n        </span>\n        <span class=\"text\">" . $manageText . "</span>\n    </button>\n</form>";
    }
    public function adminServicesTabOutput(array $params, \WHMCS\MarketConnect\OrderInformation $orderInformation = NULL, array $actionButtons = NULL)
    {
        $orderInfo = \WHMCS\MarketConnect\OrderInformation::factory($params);
        $actionBtns = array(array("icon" => "fa-cog", "label" => "Attempt Configuration", "class" => "btn-default", "moduleCommand" => "resend_configuration_data", "applicableStatuses" => array("Awaiting Configuration")), array("icon" => "fa-sign-in", "label" => "Login to SiteLock VPN Dashboard", "class" => "btn-default", "moduleCommand" => "admin_sso", "applicableStatuses" => array("Active")));
        return parent::adminServicesTabOutput($params, $orderInfo, $actionBtns);
    }
    public function isEligibleForUpgrade()
    {
        return false;
    }
}

?>