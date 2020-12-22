<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$isDefault = "";
$description = "";
$existingGatewayModule = "";
$gatewayName = "";
$defaultDisabled = "";
$gatewayToken = "";
$isBankAccount = false;
if (isset($payMethod)) {
    $isDefault = $payMethod->isDefaultPayMethod() ? "checked" : "";
    $description = "value=\"" . $payMethod->getDescription() . "\"";
    $gateway = $payMethod->getGateway();
    if ($gateway) {
        $existingGatewayModule = $gateway->getLoadedModule();
        $gatewayName = $gateway->getConfiguration()["FriendlyName"]["Value"];
    }
    $payment = $payMethod->payment;
    if ($payment instanceof WHMCS\Payment\PayMethod\Adapter\BankAccountModel) {
        $isBankAccount = true;
        if ($payment instanceof WHMCS\Payment\Contracts\RemoteTokenDetailsInterface) {
            $values["inputGatewayId"] = $payment->getRemoteToken();
            $gatewayToken = $payment->getRemoteToken();
        }
    }
} else {
    if ($forceDefault) {
        $isDefault = "checked=\"checked\"";
        $defaultDisabled = "disabled=\"disabled\"";
    }
}
if ($existingGatewayModule) {
    echo "    <div class=\"form-group\">\n        <label>";
    echo AdminLang::trans("global.gateway");
    echo "</label><br>\n        ";
    if ($payment->isMigrated()) {
        echo "            <select name=\"gateway_name\" class=\"form-control\">\n                ";
        foreach ($alternativeGateways as $gatewayModule => $displayName) {
            echo "                    <option value=\"";
            echo $gatewayModule;
            echo "\"";
            if ($gatewayModule == $existingGatewayModule) {
                echo " selected";
            }
            echo ">\n                        ";
            echo $displayName;
            echo "                    </option>\n                ";
        }
        echo "            </select>\n        ";
    } else {
        echo "            ";
        echo $gatewayName;
        echo "        ";
    }
    echo "    </div>\n    ";
}
echo "<div class=\"row\">\n    <div class=\"col-sm-12\">\n        <div class=\"form-group\">\n            <label for=\"inputDescription\">";
echo AdminLang::trans("global.description");
echo "</label>\n            <input type=\"text\"\n                id=\"inputDescription\"\n                name=\"description\"\n                ";
echo $description;
echo "                class=\"form-control\"\n                placeholder=\"";
echo AdminLang::trans("global.optional");
echo "\">\n        </div>\n    </div>\n</div>\n\n";
$this->insert("client/paymethods/partials/details-billing-contact");
if ($isBankAccount && $gatewayToken) {
    echo "    <div class=\"row\">\n        <div class=\"col-sm-12\">\n            <div class=\"form-group\">\n                <label for=\"inputGatewayToken\">\n                    ";
    echo AdminLang::trans("payments.gatewayToken");
    echo "                </label>\n                <input class=\"form-control\" id=\"inputGatewayToken\" type=\"text\" value=\"";
    echo WHMCS\Input\Sanitize::encodeToCompatHTML($gatewayToken);
    echo "\" readonly/>\n            </div>\n        </div>\n    </div>\n    ";
}
echo "\n<div class=\"row\">\n    <div class=\"col-sm-12\">\n        <label class=\"bottom-margin-10\">\n            <input type=\"checkbox\" id=\"inputIsDefault\" name=\"isDefault\" ";
echo $isDefault;
echo " ";
echo $defaultDisabled;
echo ">\n            ";
echo AdminLang::trans("payments.useDefault");
echo "        </label>\n    </div>\n</div>\n";
if ($storageGateway) {
    echo "    <input type=\"hidden\" name=\"user_id\" value=\"";
    echo $client->id;
    echo "\">\n    <input type=\"hidden\" name=\"storageGateway\" value=\"";
    echo $storageGateway;
    echo "\" />\n    ";
}

?>