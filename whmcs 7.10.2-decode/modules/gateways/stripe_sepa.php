<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
function stripe_sepa_MetaData()
{
    return array("APIVersion" => 1.1, "gatewayType" => WHMCS\Module\Gateway::GATEWAY_BANK, "failedEmail" => "Direct Debit Payment Failed", "successEmail" => "Direct Debit Payment Confirmation", "pendingEmail" => "Direct Debit Payment Pending", "noCurrencyConversion" => true, "supportedCurrencies" => array("EUR"));
}
function stripe_sepa_config()
{
    $config = array("FriendlyName" => array("Type" => "System", "Value" => "Stripe SEPA"), "publishableKey" => array("FriendlyName" => "Stripe Publishable API Key", "Type" => "text", "Size" => "30", "Description" => "Your publishable API key identifies your website to Stripe during communications. " . "This can be obtained from <a href=\"https://dashboard.stripe.com/account/apikeys\" class=\"autoLinked\">here</a>"), "secretKey" => array("FriendlyName" => "Stripe Secret API Key", "Type" => "text", "Size" => "30", "Description" => "Your secret API Key ensures only communications from Stripe are validated."), "webhookEndpointSecret" => array("FriendlyName" => "Stripe SEPA WebHook Endpoint Secret", "Type" => "password", "Size" => "30", "Description" => "Automatically generated web-hook secret key."), "statementDescriptor" => array("FriendlyName" => "Statement Descriptor", "Type" => "text", "Size" => 25, "Default" => "{CompanyName}", "Description" => "Available merge field tags: <strong>{CompanyName} {InvoiceNumber}</strong>\n<div class=\"alert alert-info top-margin-5 bottom-margin-5\">\n    Displayed on your customer's bank statement.<br />\n    <strong>Maximum of 22 characters</strong>.<br />\n    This will be set as the Statement descriptor defined in the Stripe Account.\n</div>"));
    try {
        WHMCS\Module\Gateway::factory("stripe");
        $config["copyStripeConfig"] = array("FriendlyName" => "Use Stripe Configuration", "Type" => "yesno", "Description" => "Use the configuration from Stripe to configure the Publishable Key," . " Private Key and Statement Descriptor");
    } catch (Exception $e) {
    }
    $currencies = WHMCS\Billing\Currency::where("code", "!=", "EUR")->pluck("code");
    $usageNotes = array();
    if (count($currencies)) {
        $usageNotes[] = "<strong>Unsupported Currencies.</strong> You have one or more " . "currencies configured that are not supported by Stripe SEPA. Invoices using " . "currencies SEPA does not support will be unable to be paid using SEPA. " . "<a href=\"https://docs.whmcs.com/Stripe_SEPA#Supported_Currencies\" target=\"_blank\">" . "Learn more</a>";
    }
    if ($usageNotes) {
        $config["UsageNotes"] = array("Type" => "System", "Value" => implode("<br>", $usageNotes));
    }
    return $config;
}
function stripe_sepa_nolocalcc()
{
}
function stripe_sepa_config_validate(array $params = array())
{
    try {
        if (array_key_exists("copyStripeConfig", $params) && $params["copyStripeConfig"]) {
            return NULL;
        }
        if ($params["publishableKey"] && substr($params["publishableKey"], 0, 3) === "pk_" && $params["secretKey"] && substr($params["secretKey"], 0, 3) === "sk_") {
            stripe_sepa_start_stripe($params);
            Stripe\Account::retrieve();
            Stripe\Stripe::setApiKey($params["publishableKey"]);
            Stripe\Account::retrieve();
        } else {
            throw new WHMCS\Exception\Module\InvalidConfiguration("Please ensure your Stripe API keys are correct and try again.");
        }
    } catch (Exception $e) {
        $checkMessage = substr($e->getMessage(), 0, 55);
        if ($checkMessage != "This API call cannot be made with a publishable API key") {
            throw new WHMCS\Exception\Module\InvalidConfiguration($e->getMessage());
        }
    }
}
function stripe_sepa_config_post_save(array $params = array())
{
    if (array_key_exists("copyStripeConfig", $params) && $params["copyStripeConfig"]) {
        try {
            $gatewayInterface = WHMCS\Module\Gateway::factory("stripe");
            $gatewayParams = $gatewayInterface->getParams();
            $copiedParams = array("publishableKey" => $gatewayParams["publishableKey"], "secretKey" => $gatewayParams["secretKey"], "statementDescriptor" => $gatewayParams["statementDescriptor"], "copyStripeConfig" => "");
            foreach ($copiedParams as $copiedParam => $value) {
                WHMCS\Database\Capsule::table("tblpaymentgateways")->updateOrInsert(array("gateway" => "stripe_sepa", "setting" => $copiedParam), array("value" => $value));
            }
            $params = array_merge($params, $copiedParams);
        } catch (Exception $e) {
        }
    }
    if (array_key_exists("secretKey", $params) && $params["secretKey"]) {
        $notificationUrl = App::getSystemURL() . "modules/gateways/callback/stripe_sepa.php";
        stripe_sepa_start_stripe($params);
        $webHooks = Stripe\WebhookEndpoint::all(array());
        foreach ($webHooks->data as $webHook) {
            if ($webHook->url == $notificationUrl && $webHook->status == "enabled") {
                return NULL;
            }
        }
        $webHook = Stripe\WebhookEndpoint::create(array("url" => $notificationUrl, "enabled_events" => array("charge.failed", "charge.succeeded")));
        WHMCS\Database\Capsule::table("tblpaymentgateways")->updateOrInsert(array("gateway" => "stripe_sepa", "setting" => "webhookEndpointSecret"), array("value" => $webHook->secret));
    }
}
function stripe_sepa_deactivate(array $params)
{
    $notificationUrl = App::getSystemURL() . "modules/gateways/callback/stripe_sepa.php";
    stripe_sepa_start_stripe($params);
    $webHooks = Stripe\WebhookEndpoint::all(array());
    foreach ($webHooks->data as $webHook) {
        if ($webHook->url == $notificationUrl && $webHook->status == "enabled") {
            $webHook->delete();
        }
    }
}
function stripe_sepa_storeremote(array $params)
{
    stripe_sepa_start_stripe($params);
    switch ($params["action"]) {
        case "create":
            $customerId = stripe_sepa_findFirstStripeCustomerId($params["clientdetails"]["model"]);
            if (!$customerId) {
                $customerId = stripe_sepa_create_customer($params);
            }
            $customer = Stripe\Customer::retrieve($customerId);
            $remoteToken = $params["remoteStorageToken"];
            if (substr($remoteToken, 0, 3) !== "src") {
                return array("status" => "error", "rawdata" => array("message" => "Invalid Remote Token", "token" => $remoteToken));
            }
            try {
                $source = Stripe\Customer::createSource($customer->id, array("source" => $remoteToken));
                $accountNumber = $source->sepa_debit->last4;
                return array("status" => "success", "rawdata" => $customer->jsonSerialize(), "remoteToken" => json_encode(array("customer" => $customer->id, "account" => $source->id, "mandate" => $source->sepa_debit->mandate_reference)), "accountNumber" => $accountNumber);
            } catch (Exception $e) {
                return array("status" => "error", "rawdata" => $e->getMessage());
            }
            break;
        case "delete":
            try {
                $remoteToken = stripe_sepa_parseGatewayToken($params["gatewayid"]);
                if (!$remoteToken) {
                    return array("status" => "error", "rawdata" => array("error" => "Invalid Remote Token for Gateway", "data" => $params["gatewayid"]));
                }
                Stripe\Customer::deleteSource($remoteToken["customer"], $remoteToken["account"]);
                return array("status" => "success");
            } catch (Exception $e) {
                return array("status" => "error", "rawdata" => $e->getMessage());
            }
            break;
        case "update":
            return array("status" => "error", "rawdata" => "Updating Bank Accounts is not possible");
        default:
            return array("status" => "error", "rawdata" => "Invalid Action Request");
    }
}
function stripe_sepa_capture(array $params)
{
    try {
        stripe_sepa_start_stripe($params);
        $remoteToken = stripe_sepa_parseGatewayToken($params["gatewayid"]);
        if (!$remoteToken) {
            throw new InvalidArgumentException("Invalid Remote Token For Gateway: " . $params["gatewayid"]);
        }
        if ($params["currency"] != "EUR") {
            throw new InvalidArgumentException("Invalid Currency For Gateway: " . $params["currency"]);
        }
        $charge = Stripe\Charge::create(array("amount" => stripe_sepa_formatAmount($params["amount"], $params["currency"]), "currency" => strtolower($params["currency"]), "customer" => $remoteToken["customer"], "source" => $remoteToken["account"], "metadata" => array("id" => $params["invoiceid"], "invoiceNumber" => $params["invoicenum"], "webhookProtection" => substr(sha1(App::getLicense()->getLicenseKey()), 0, 16)), "statement_descriptor" => stripe_sepa_statement_descriptor($params)));
        return array("status" => "pending", "rawdata" => array("charge" => $charge->jsonSerialize()));
    } catch (Exception $e) {
        return array("status" => "error", "rawdata" => array("gatewayId" => $params["gatewayid"], "currency" => $params["currency"], "message" => $e->getMessage()), "declinereason" => $e->getMessage());
    }
}
function stripe_sepa_bank_account_input(array $params)
{
    $existingSubmittedToken = "";
    $assetHelper = DI::make("asset");
    $now = time();
    $token = App::getFromRequest("remoteStorageToken");
    if ($token && substr($token, 0, 4) != "btok") {
        $token = "";
    }
    if (!$token && $params["gatewayid"]) {
        $remoteToken = stripe_sepa_parseGatewayToken($params["gatewayid"]);
        if ($remoteToken && array_key_exists("account", $remoteToken)) {
            $existingSubmittedToken = $remoteToken["account"];
        }
    }
    if ($token) {
        $existingSubmittedToken = $token;
    }
    $jsOutput = "existingToken = '" . $existingSubmittedToken . "';";
    $companyName = WHMCS\Config\Setting::getValue("CompanyName");
    $sepaJs = $assetHelper->getWebRoot() . "/modules/gateways/stripe_sepa/stripe_sepa.min.js?a=" . $now;
    $lang = array("iban" => Lang::trans("paymentMethods.iban"), "mandate_acceptance" => Lang::trans("paymentMethods.mandateAcceptance", array(":companyName" => WHMCS\Config\Setting::getValue("CompanyName"))));
    $apiVersion = WHMCS\Module\Gateway\Stripe\Constant::$apiVersion;
    return "<script type=\"text/javascript\" src=\"" . $sepaJs . "\"></script>\n<script type=\"text/javascript\">\n\nvar existingToken = null,\n    companyName = '" . $companyName . "',\n    clientEmail = '" . $params["clientdetails"]["email"] . "',\n    stripe = null,\n    elements = null,\n    iban = null,\n    lang = null;\n\n\$(document).ready(function() {\n    stripe = Stripe('" . $params["publishableKey"] . "');\n    stripe.api_version = \"" . $apiVersion . "\";\n    elements = stripe.elements();\n    iban = elements.create('iban', {\n        supportedCountries: ['SEPA'],\n    });\n    lang = {\n        iban: '" . $lang["iban"] . "',\n        mandate_acceptance: '" . $lang["mandate_acceptance"] . "',\n    };\n    " . $jsOutput . "\n    initStripeSEPA();\n});    \n</script>";
}
function stripe_sepa_refund(array $params = array())
{
    $amount = stripe_sepa_formatAmount($params["amount"], $params["currency"]);
    stripe_sepa_start_stripe($params);
    $client = WHMCS\User\Client::find($params["clientdetails"]["userid"]);
    try {
        $transaction = Stripe\BalanceTransaction::retrieve($params["transid"]);
        $refund = Stripe\Refund::create(array("charge" => $transaction->source, "amount" => $amount));
        $refundTransaction = Stripe\BalanceTransaction::retrieve($refund->balance_transaction);
        $transactionFeeCurrency = WHMCS\Database\Capsule::table("tblcurrencies")->where("code", "=", strtoupper($refundTransaction->fee_details[0]->currency))->first(array("id"));
        $refundTransactionFee = 0;
        if ($transactionFeeCurrency) {
            $refundTransactionFee = convertCurrency($refundTransaction->fee / -100, $transactionFeeCurrency->id, $params["convertto"] ?: $client->currencyId);
        }
        return array("transid" => $refundTransaction->id, "rawdata" => array_merge($refund->jsonSerialize(), $refundTransaction->jsonSerialize()), "status" => "success", "fees" => $refundTransactionFee);
    } catch (Exception $e) {
        return array("status" => "error", "rawdata" => $e->getMessage());
    }
}
function stripe_sepa_formatValue($value)
{
    return $value !== "" ? $value : NULL;
}
function stripe_sepa_formatAmount($amount, $currencyCode)
{
    $currenciesWithoutDecimals = array("BIF", "CLP", "DJF", "GNF", "JPY", "KMF", "KRW", "MGA", "PYG", "RWF", "VND", "VUV", "XAF", "XOF", "XPF");
    $currencyCode = strtoupper($currencyCode);
    $isNoDecimalCurrency = in_array($currencyCode, $currenciesWithoutDecimals);
    $amount = str_replace(array(",", "."), "", $amount);
    if ($isNoDecimalCurrency) {
        $amount = round($amount / 100);
    }
    return $amount;
}
function stripe_sepa_start_stripe(array $params)
{
    Stripe\Stripe::setAppInfo(WHMCS\Module\Gateway\Stripe\Constant::$appName, App::getVersion()->getMajor(), WHMCS\Module\Gateway\Stripe\Constant::$appUrl, WHMCS\Module\Gateway\Stripe\Constant::$appPartnerId);
    Stripe\Stripe::setApiKey($params["secretKey"]);
    Stripe\Stripe::setApiVersion(WHMCS\Module\Gateway\Stripe\Constant::$apiVersion);
}
function stripe_sepa_parseGatewayToken($data)
{
    $data = json_decode($data, true);
    if ($data && is_array($data)) {
        return $data;
    }
    return array();
}
function stripe_sepa_findFirstCustomerToken(WHMCS\User\Contracts\ContactInterface $client)
{
    $clientToUse = $client;
    if ($clientToUse instanceof WHMCS\User\Client\Contact) {
        $clientToUse = $clientToUse->client;
    }
    foreach ($clientToUse->payMethods as $payMethod) {
        if ($payMethod->gateway_name == "stripe_sepa") {
            $payment = $payMethod->payment;
            $token = stripe_sepa_parsegatewaytoken($payment->getRemoteToken());
            if ($token) {
                return $token;
            }
        }
    }
}
function stripe_sepa_findFirstStripeCustomerId(WHMCS\User\Contracts\ContactInterface $client)
{
    $clientToUse = $client;
    if ($clientToUse instanceof WHMCS\User\Client\Contact) {
        $clientToUse = $clientToUse->client;
    }
    foreach ($clientToUse->payMethods as $payMethod) {
        if (in_array($payMethod->gateway_name, array("stripe", "stripe_ach", "stripe_sepa"))) {
            $payment = $payMethod->payment;
            $token = stripe_sepa_parsegatewaytoken($payment->getRemoteToken());
            if ($token) {
                return $token["customer"];
            }
        }
    }
}
function stripe_sepa_statement_descriptor(array $params)
{
    $invoiceNumber = array_key_exists("invoicenum", $params) && $params["invoicenum"] ? $params["invoicenum"] : $params["invoiceid"];
    return substr(str_replace(array("{CompanyName}", "{InvoiceNumber}", ">", "<", "'", "\""), array(WHMCS\Config\Setting::getValue("CompanyName"), $invoiceNumber, "", "", "", ""), $params["statementDescriptor"]), -22);
}
function stripe_sepa_create_customer(array $params)
{
    $client = $params["clientdetails"]["model"];
    if ($client instanceof WHMCS\User\Client\Contact) {
        $client = $client->client;
    }
    $stripeCustomer = Stripe\Customer::create(array("description" => "Customer for " . $client->fullName . " (" . $client->email . ")", "email" => $client->email, "metadata" => array("id" => $client->id, "fullName" => $client->fullName, "email" => $client->email)));
    return $stripeCustomer->id;
}

?>