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
function stripe_MetaData()
{
    return array("APIVersion" => 1.1);
}
function _stripe_formatValue($value)
{
    return $value !== "" ? $value : NULL;
}
function _stripe_formatAmount($amount, $currencyCode)
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
function stripe_config()
{
    $config = array("FriendlyName" => array("Type" => "System", "Value" => "Stripe"), "publishableKey" => array("FriendlyName" => "Stripe Publishable API Key", "Type" => "text", "Size" => "30", "Description" => "Your publishable API key identifies your website to Stripe during communications. " . "This can be obtained from <a href=\"https://dashboard.stripe.com/account/apikeys\" class=\"autoLinked\">here</a>"), "secretKey" => array("FriendlyName" => "Stripe Secret API Key", "Type" => "text", "Size" => "30", "Description" => "Your secret API Key ensures only communications from Stripe are validated."), "statementDescriptor" => array("FriendlyName" => "Statement Descriptor Suffix", "Type" => "text", "Size" => 25, "Default" => "{CompanyName}", "Description" => "Available merge field tags: <strong>{CompanyName} {InvoiceNumber}</strong>\n<div class=\"alert alert-info top-margin-5 bottom-margin-5\">\n    Displayed on your customer's credit card statement.<br />\n    <strong>Maximum of 22 characters</strong>.<br />\n    This will be appended to the Statement descriptor defined in the Stripe Account.\n</div>"), "applePay" => array("FriendlyName" => "Allow Payment Request Buttons", "Type" => "yesno", "Description" => "Tick to enable showing the Payment Request buttons on supported devices." . " <a href=\"https://docs.whmcs.com/Stripe#Payment_Request_Button\" class=\"autoLinked\">" . "Learn More</a>"));
    $hooksPath = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "hooks" . DIRECTORY_SEPARATOR;
    if (file_exists($hooksPath . "stripe.php")) {
        $config["existingHook"] = array("FriendlyName" => "Existing Hook", "Description" => "<div class=\"alert alert-danger top-margin-5 bottom-margin-5\">\n    We have detected the presence of a stripe.php hook file in " . $hooksPath . ".<br />\n    This is a file commonly present when using a third party Stripe module.<br />\n    To use the official WHMCS module, any previous third party modules must be fully uninstalled/removed.\n</div>");
    }
    $systemTemplate = ROOTDIR . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . WHMCS\Config\Setting::getValue("Template") . DIRECTORY_SEPARATOR;
    $orderTemplate = WHMCS\View\Template\OrderForm::factory();
    $searchText = "gateway-errors";
    $mainTemplateFiles = array("account-paymentmethods-manage.tpl");
    $templatesToUpdate = array();
    foreach ($mainTemplateFiles as $templateFile) {
        $templateContents = file_get_contents($systemTemplate . $templateFile);
        if (stristr($templateContents, $searchText) === false) {
            $templatesToUpdate[] = $systemTemplate . $templateFile;
        }
    }
    $templateContents = "";
    if ($orderTemplate->hasTemplate("checkout", false)) {
        $templateContents = file_get_contents($orderTemplate->getTemplatePath() . "checkout.tpl");
        $orderTemplate = $orderTemplate->getTemplatePath() . "checkout.tpl";
    } else {
        if ($orderTemplate->hasTemplate("checkout")) {
            $templateContents = file_get_contents($orderTemplate->getParent()->getTemplatePath() . "checkout.tpl");
            $orderTemplate = $orderTemplate->getParent()->getTemplatePath() . "checkout.tpl";
        } else {
            if ($orderTemplate->hasTemplate("viewcart", false)) {
                $templateContents = file_get_contents($orderTemplate->getTemplatePath() . "viewcart.tpl");
                $orderTemplate = $orderTemplate->getTemplatePath() . "viewcart.tpl";
            } else {
                if ($orderTemplate->hasTemplate("viewcart")) {
                    $templateContents = file_get_contents($orderTemplate->getParent()->getTemplatePath() . "viewcart.tpl");
                    $orderTemplate = $orderTemplate->getParent()->getTemplatePath() . "viewcart.tpl";
                } else {
                    $orderTemplate = NULL;
                }
            }
        }
    }
    if ($orderTemplate && stristr($templateContents, $searchText) === false) {
        $templatesToUpdate[] = $orderTemplate;
    }
    if ($templatesToUpdate) {
        $config["templateChanges"] = array("FriendlyName" => "Template Changes", "Description" => "<div class=\"alert alert-danger top-margin-5 bottom-margin-5\">\n    Required Template Changes Not Found: We were unable to detect the presence of the required WHMCS 7.1 template changes for Stripe compatibility in your active order form or client area template. Please ensure the changes itemised in the 7.1 upgrade here have been applied. Please see <a href=\"https://docs.whmcs.com/Version_7.1_Release_Notes#Template_Changes\" class=\"autoLinked\">Template Changes</a> for more information.\n</div>");
    }
    return $config;
}
function stripe_config_validate(array $params = array())
{
    try {
        if ($params["publishableKey"] && substr($params["publishableKey"], 0, 3) === "pk_" && $params["secretKey"] && substr($params["secretKey"], 0, 3) === "sk_") {
            stripe_start_stripe($params);
            Stripe\Account::retrieve();
            Stripe\Stripe::setApiKey($params["publishableKey"]);
            Stripe\Account::retrieve();
        } else {
            throw new WHMCS\Exception\Module\InvalidConfiguration("Please ensure your Stripe API keys are correct and try again.");
        }
    } catch (Exception $e) {
        if (substr($e->getMessage(), 0, 55) != "This API call cannot be made with a publishable API key") {
            throw new WHMCS\Exception\Module\InvalidConfiguration($e->getMessage());
        }
    }
}
function stripe_capture(array $params = array())
{
    $stripeCustomer = $params["gatewayid"];
    $method = NULL;
    $intent = NULL;
    $newMethod = false;
    $return = array();
    stripe_start_stripe($params);
    if ($stripeCustomer) {
        $jsonCheck = json_decode(WHMCS\Input\Sanitize::decode($stripeCustomer), true);
        if (is_array($jsonCheck) && array_key_exists("customer", $jsonCheck)) {
            $stripeCustomer = $jsonCheck["customer"];
            $method = Stripe\PaymentMethod::retrieve($jsonCheck["method"]);
            if ($stripeCustomer != $method->customer) {
                $stripeCustomer = $method->customer;
                $return["gatewayid"] = json_encode(array("customer" => $stripeCustomer, "method" => $method->id));
            }
        }
    }
    if (substr($stripeCustomer, 0, 3) != "cus") {
        $stripeCustomer = "";
    }
    $amount = _stripe_formatamount($params["amount"], $params["currency"]);
    $client = WHMCS\User\Client::find($params["clientdetails"]["userid"]);
    $billingDetails = array();
    if ($params["cardnum"] || !$method) {
        $billingDetails = array("name" => $params["clientdetails"]["fullname"], "email" => $params["clientdetails"]["email"], "address" => array("country" => $params["clientdetails"]["country"]));
        if (array_key_exists("address1", $params["clientdetails"])) {
            $billingDetails["address"]["line1"] = _stripe_formatvalue($params["clientdetails"]["address1"]);
        }
        if (array_key_exists("address2", $params["clientdetails"])) {
            $billingDetails["address"]["line2"] = _stripe_formatvalue($params["clientdetails"]["address2"]);
        }
        if (array_key_exists("city", $params["clientdetails"])) {
            $billingDetails["address"]["city"] = _stripe_formatvalue($params["clientdetails"]["city"]);
        }
        if (array_key_exists("state", $params["clientdetails"])) {
            $billingDetails["address"]["state"] = _stripe_formatvalue($params["clientdetails"]["state"]);
        }
        if (array_key_exists("postcode", $params["clientdetails"])) {
            $billingDetails["address"]["postal_code"] = _stripe_formatvalue($params["clientdetails"]["postcode"]);
        }
    }
    if ($params["cardnum"]) {
        try {
            $card = array("number" => $params["cardnum"], "exp_month" => substr($params["cardexp"], 0, 2), "exp_year" => substr($params["cardexp"], 2));
            if ($params["cccvv"]) {
                $card["cvc"] = $params["cccvv"];
            }
            $method = Stripe\PaymentMethod::create(array("type" => "card", "card" => $card, "billing_details" => $billingDetails));
            $newMethod = true;
        } catch (Exception $e) {
            return array("status" => "error", "rawdata" => $e->getMessage());
        }
    }
    if (!$method && $stripeCustomer) {
        $remoteCustomer = Stripe\Customer::retrieve($stripeCustomer);
        $source = $remoteCustomer->default_source;
        if ($source) {
            $method = Stripe\PaymentMethod::retrieve($source);
            $newMethod = true;
        }
    }
    if ($newMethod) {
        if (!$stripeCustomer) {
            try {
                $remoteToken = stripe_findFirstCustomerToken($client);
                if ($remoteToken && array_key_exists("customer", $remoteToken)) {
                    $stripeCustomer = $remoteToken["customer"];
                }
                if (!$stripeCustomer || substr($stripeCustomer, 0, 4) !== "cus_") {
                    $stripeCustomer = Stripe\Customer::create(array("description" => "Customer for " . $client->fullName . " (" . $client->email . ")", "email" => $client->email, "metadata" => array("id" => $client->id, "fullName" => $client->fullName, "email" => $client->email)));
                    $stripeCustomer = $stripeCustomer->id;
                }
            } catch (Exception $e) {
                return array("status" => "error", "rawdata" => $e->getMessage());
            }
        }
        $remoteToken = json_encode(array("customer" => $stripeCustomer, "method" => $method->id));
        $return = array("gatewayid" => $remoteToken);
        if ($stripeCustomer && !$method->customer) {
            try {
                $method->attach(array("customer" => $stripeCustomer));
            } catch (Exception $e) {
                $status = "error";
                if ($e instanceof WHMCS\Exception\Gateways\Declined || $e instanceof Stripe\Error\Card) {
                    $status = "declined";
                }
                return array("status" => $status, "rawdata" => $e->getMessage(), "declinereason" => $e->getMessage());
            }
        }
    }
    try {
        $paymentIntent = WHMCS\Session::getAndDelete("PaymentIntent" . $params["invoiceid"]);
        if (!$paymentIntent) {
            $paymentIntent = App::getFromRequest("remoteStorageToken");
        }
        if ($paymentIntent && substr($paymentIntent, 0, 2) == "pi") {
            $intent = Stripe\PaymentIntent::retrieve($paymentIntent);
            if ($intent->status == "requires_capture") {
                $intent->capture();
            }
            if ($intent->status != "succeeded") {
                throw new WHMCS\Exception\Gateways\Declined($intent->last_payment_error);
            }
            $charge = $intent->charges->data[0];
        } else {
            if (!$stripeCustomer || !$method) {
                throw new InvalidArgumentException("Missing Stripe Customer or Payment Method - Please Try Again");
            }
            $description = stripe_statement_descriptor($params);
            $intent = Stripe\PaymentIntent::create(array("amount" => $amount, "currency" => strtolower($params["currency"]), "customer" => $method->customer, "payment_method" => $method->id, "description" => $params["description"], "metadata" => array("id" => $params["invoiceid"], "invoiceNumber" => $params["invoicenum"]), "statement_descriptor_suffix" => $description, "confirm" => true, "off_session" => true));
            if ($intent->status == "requires_capture") {
                $intent->capture();
            }
            if ($intent->status != "succeeded") {
                $error = $intent->last_payment_error;
                if (!$error) {
                    $error = "Cardholder Action Required";
                }
                throw new WHMCS\Exception\Gateways\Declined($error);
            }
            $charge = $intent->charges->data[0];
        }
        $transaction = Stripe\BalanceTransaction::retrieve($charge->balance_transaction);
        $transactionFeeCurrency = WHMCS\Database\Capsule::table("tblcurrencies")->where("code", "=", strtoupper($transaction->fee_details[0]->currency))->first(array("id"));
        $transactionId = $transaction->id;
        $transactionFee = 0;
        if ($transactionFeeCurrency) {
            $transactionFee = convertCurrency($transaction->fee / 100, $transactionFeeCurrency->id, $params["convertto"] ?: $client->currencyId);
        }
        return array_merge(array("status" => "success", "transid" => $transactionId, "amount" => $params["amount"], "fee" => $transactionFee, "rawdata" => array("charge" => $charge->jsonSerialize(), "transaction" => $transaction->jsonSerialize())), $return);
    } catch (Exception $e) {
        $status = "error";
        if ($e instanceof WHMCS\Exception\Gateways\Declined || $e instanceof Stripe\Error\Card) {
            $status = "declined";
        }
        $data = array();
        if ($intent && in_array($intent->status, array("requires_source_action", "requires_action", "requires_capture"))) {
            $intent->cancel(array("cancellation_reason" => "abandoned"));
            $data = $intent->jsonSerialize();
        }
        $data["error"] = $e->getMessage();
        if (method_exists($e, "getJsonBody")) {
            $data["detail"] = $e->getJsonBody();
        }
        return array("status" => $status, "rawdata" => $data, "declinereason" => $e->getMessage());
    }
}
function stripe_post_checkout(array $params = array())
{
    $amount = _stripe_formatamount($params["amount"], $params["currency"]);
    $token = WHMCS\Session::getAndDelete("remoteStorageToken");
    WHMCS\Session::delete("cartccdetail");
    stripe_start_stripe($params);
    $client = WHMCS\User\Client::find($params["clientdetails"]["id"]);
    $stripeCustomer = "";
    $intent = NULL;
    $method = NULL;
    $returnData = array();
    if (substr($token, 0, 2) == "pi") {
        $intent = Stripe\PaymentIntent::retrieve($token);
        $method = Stripe\PaymentMethod::retrieve($intent->payment_method);
        if ($intent->customer) {
            $stripeCustomer = $intent->customer;
        }
        $description = stripe_statement_descriptor($params);
        if (in_array($intent->status, array(Stripe\SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD, Stripe\SetupIntent::STATUS_REQUIRES_CONFIRMATION, "requires_capture"))) {
            $intent->statement_descriptor_suffix = substr($description, 0, 22);
        }
        $intent->description = $params["description"];
        $intent->save();
    }
    if (WHMCS\Session::getAndDelete("StripeCardId") === "new") {
        $token = $method;
    }
    if (substr($token, 0, 2) == "pm") {
        $method = Stripe\PaymentMethod::retrieve($token);
    }
    if (!$amount || $amount === "000") {
        if ($intent) {
            $intent->cancel();
        }
    } else {
        try {
            if (!$stripeCustomer && $method && $method->customer) {
                $stripeCustomer = $method->customer;
            }
            if (!$stripeCustomer) {
                $stripeCustomer = $params["gatewayid"];
            }
            if ($stripeCustomer && substr($stripeCustomer, 0, 3) != "cus") {
                $jsonCheck = json_decode(WHMCS\Input\Sanitize::decode($stripeCustomer), true);
                if (is_array($jsonCheck) && array_key_exists("customer", $jsonCheck)) {
                    $stripeCustomer = $jsonCheck["customer"];
                }
            }
            if ($stripeCustomer && substr($stripeCustomer, 0, 3) != "cus") {
                $stripeCustomer = "";
            }
            if ($token) {
                if (!$method->customer) {
                    $method->attach(array("customer" => $stripeCustomer));
                }
                $card = $method->jsonSerialize()["card"];
                $remoteToken = json_encode(array("customer" => $stripeCustomer, "method" => $method->id));
                $returnData = array("cardnumber" => $card["last4"], "cardexpiry" => sprintf("%02d%02d", $card["exp_month"], substr($card["exp_year"], 2)), "cardtype" => ucfirst($card["brand"]), "gatewayid" => $remoteToken);
            }
            if (!$stripeCustomer) {
                return array("status" => "error", "rawdata" => "No Stripe Customer Details Found");
            }
            if ($intent->status == "requires_capture") {
                $intent->capture(array("amount_to_capture" => $amount));
            }
            if ($intent->status != "succeeded") {
                throw new WHMCS\Exception\Gateways\Declined($intent->last_payment_error);
            }
            $charge = $intent->charges->data[0];
            $transaction = Stripe\BalanceTransaction::retrieve($charge->balance_transaction);
            $transactionFeeCurrency = WHMCS\Database\Capsule::table("tblcurrencies")->where("code", "=", strtoupper($transaction->fee_details[0]->currency))->first(array("id"));
            $transactionId = $transaction->id;
            $transactionFee = 0;
            if ($transactionFeeCurrency) {
                $transactionFee = convertCurrency($transaction->fee / 100, $transactionFeeCurrency->id, $params["convertto"] ?: $client->currencyId);
            }
            $amount = $params["amount"];
            if (array_key_exists("convertto", $params) && $params["convertto"]) {
                $amount = $params["basecurrencyamount"];
            }
            return array_merge(array("status" => "success", "transid" => $transactionId, "amount" => $amount, "fee" => $transactionFee, "rawdata" => array("charge" => $charge->jsonSerialize(), "transaction" => $transaction->jsonSerialize())), $returnData);
        } catch (Exception $e) {
            $status = "error";
            if ($e instanceof WHMCS\Exception\Gateways\Declined || $e instanceof Stripe\Error\Card) {
                $status = "declined";
            }
            $data = array();
            if ($intent && in_array($intent->status, array("requires_source_action", "requires_action", "requires_capture"))) {
                $intent->cancel(array("cancellation_reason" => "abandoned"));
                $data = $intent->jsonSerialize();
            }
            $data["error"] = $e->getMessage();
            WHMCS\Session::set("StripeDeclined" . $params["invoiceid"], true);
            return array("status" => $status, "rawdata" => $data);
        }
    }
}
function stripe_fraud_check_fail(array $params)
{
    $token = WHMCS\Session::getAndDelete("remoteStorageToken");
    stripe_start_stripe($params);
    if (substr($token, 0, 2) == "pi") {
        $intent = Stripe\PaymentIntent::retrieve($token);
        $intent->cancel(array("cancellation_reason" => "fraudulent"));
    }
}
function stripe_storeremote(array $params = array())
{
    $action = $params["action"];
    $amount = (double) $params["amount"];
    if (WHMCS\Session::get("cartccdetail") && $amount) {
        WHMCS\Session::set("StripeCardId", App::getFromRequest("ccinfo"));
        return array();
    }
    if ($action == "delete" && App::isInRequest("ccinfo") && App::getFromRequest("ccinfo") == "new") {
        $action = "create";
    }
    stripe_start_stripe($params);
    if ($action == "create") {
        $token = WHMCS\Session::getAndDelete("remoteStorageToken");
        if (!$token && App::isInRequest("remoteStorageToken")) {
            $token = (string) App::getFromRequest("remoteStorageToken");
        }
        $intent = NULL;
        $method = NULL;
        if (substr($token, 0, 2) == "pi") {
            WHMCS\Session::set("PaymentIntent" . $params["invoiceid"], $token);
            $intent = Stripe\PaymentIntent::retrieve($token);
            $method = Stripe\PaymentMethod::retrieve($intent->payment_method);
        }
        if (substr($token, 0, 3) == "tok") {
            $method = Stripe\PaymentMethod::create($token);
        }
        $setupIntent = NULL;
        if (substr($token, 0, 4) == "seti") {
            $setupIntent = Stripe\SetupIntent::retrieve($token);
            $method = Stripe\PaymentMethod::retrieve($setupIntent->payment_method);
        }
        if (substr($token, 0, 2) == "pm") {
            $method = Stripe\PaymentMethod::retrieve($token);
        }
        if (!$method) {
            return array("status" => "error", "rawdata" => array("message" => "An unexpected error - No Stripe Payment Method found from token", "token" => $token));
        }
        $stripeCustomer = $params["gatewayid"];
        if ($stripeCustomer) {
            $jsonCheck = stripe_parseGatewayToken($stripeCustomer);
            if ($jsonCheck && array_key_exists("customer", $jsonCheck)) {
                $stripeCustomer = $jsonCheck["customer"];
            }
        }
        if (substr($stripeCustomer, 0, 3) != "cus") {
            $stripeCustomer = "";
        }
        if (!$stripeCustomer && $intent) {
            $stripeCustomer = $intent->customer;
        }
        if (!$stripeCustomer && $method) {
            $stripeCustomer = $method->customer;
        }
        if (!$stripeCustomer && $setupIntent) {
            $existingToken = stripe_findFirstCustomerToken($params["clientdetails"]["model"]);
            if ($existingToken) {
                $stripeCustomer = $existingToken["customer"];
            }
        }
        if (substr($stripeCustomer, 0, 3) != "cus") {
            $stripeCustomer = "";
        }
        if (!$stripeCustomer) {
            $client = $params["clientdetails"]["model"];
            $stripeCustomer = Stripe\Customer::create(array("description" => "Customer for " . $client->fullName . " (" . $client->email . ")", "name" => $client->fullName, "email" => $client->email, "address" => array("line1" => _stripe_formatvalue($client->address1), "line2" => _stripe_formatvalue($client->address2), "city" => _stripe_formatvalue($client->city), "state" => _stripe_formatvalue($client->state), "country" => _stripe_formatvalue($client->country), "postal_code" => _stripe_formatvalue($client->postcode)), "metadata" => array("id" => $client->id, "fullName" => $client->fullName, "email" => $client->email)));
        }
        if ($stripeCustomer && is_string($stripeCustomer)) {
            $stripeCustomer = Stripe\Customer::retrieve($stripeCustomer);
        }
        if (!$method->customer) {
            $method->attach(array("customer" => $stripeCustomer->id));
        }
        if ($method) {
            $billingContact = $params["payMethod"]->contact;
            $billingDetails = array("name" => $billingContact->fullName, "address" => array("line1" => _stripe_formatvalue($billingContact->address1), "line2" => _stripe_formatvalue($billingContact->address2), "city" => _stripe_formatvalue($billingContact->city), "state" => _stripe_formatvalue($billingContact->state), "country" => _stripe_formatvalue($billingContact->country), "postal_code" => _stripe_formatvalue($billingContact->postcode)));
            try {
                Stripe\PaymentMethod::update($method->id, array("billing_details" => $billingDetails));
            } catch (Exception $e) {
            }
        }
        if ($token && $method) {
            $card = $method->jsonSerialize()["card"];
            $cardLastFour = $card["last4"];
            $cardExpiry = str_pad($card["exp_month"], 2, "0", STR_PAD_LEFT) . substr($card["exp_year"], 2);
            $cardType = $card["brand"];
            return array("nodelete" => true, "cardnumber" => $cardLastFour, "cardlastfour" => $cardLastFour, "cardexpiry" => $cardExpiry, "cardtype" => ucfirst($cardType), "gatewayid" => json_encode(array("customer" => $stripeCustomer->id, "method" => $method->id)), "status" => "success", "rawdata" => $stripeCustomer->jsonSerialize());
        }
    } else {
        if ($params["action"] == "update") {
            $stripeCustomer = $params["remoteStorageToken"];
            $method = NULL;
            if ($stripeCustomer && substr($stripeCustomer, 0, 3) === "cus") {
                $stripeCustomer = Stripe\Customer::retrieve($stripeCustomer);
                $source = $stripeCustomer->default_source;
                if ($source) {
                    $method = Stripe\PaymentMethod::retrieve($source);
                    $params["gatewayid"] = json_encode(array("customer" => $stripeCustomer->id, "method" => $method->id));
                    $method = $method->id;
                }
            }
            if ($stripeCustomer) {
                if (is_string($stripeCustomer)) {
                    $jsonCheck = stripe_parseGatewayToken($stripeCustomer);
                    if ($jsonCheck && array_key_exists("customer", $jsonCheck)) {
                        $stripeCustomer = $jsonCheck["customer"];
                        $method = $jsonCheck["method"];
                        $stripeCustomer = Stripe\Customer::retrieve($stripeCustomer);
                    }
                }
                try {
                    if ($method) {
                        $billingContact = $params["payMethod"]->contact;
                        $billingContactEmail = $params["payMethod"]->client->email;
                        $billingDetails = array("name" => $billingContact->fullName, "address" => array("line1" => _stripe_formatvalue($billingContact->address1), "line2" => _stripe_formatvalue($billingContact->address2), "city" => _stripe_formatvalue($billingContact->city), "state" => _stripe_formatvalue($billingContact->state), "country" => _stripe_formatvalue($billingContact->country), "postal_code" => _stripe_formatvalue($billingContact->postcode)));
                        if (substr($method, 0, 4) != "card") {
                            $billingDetails["email"] = $billingContactEmail;
                        }
                        Stripe\PaymentMethod::update($method, array("card" => array("exp_month" => $params["cardExpiryMonth"], "exp_year" => $params["cardExpiryYear"]), "billing_details" => $billingDetails));
                    }
                    return array("status" => "success", "cardexpiry" => $params["cardexp"], "gatewayid" => $params["gatewayid"], "rawdata" => $stripeCustomer->jsonSerialize());
                } catch (Exception $e) {
                    return array("status" => "error", "rawdata" => array("customer" => $stripeCustomer, "error" => $e->getMessage()));
                }
            }
        } else {
            if ($params["action"] == "delete") {
                $stripeCustomer = $params["gatewayid"];
                $method = NULL;
                if ($stripeCustomer) {
                    $jsonCheck = stripe_parseGatewayToken($stripeCustomer);
                    if ($jsonCheck && array_key_exists("customer", $jsonCheck)) {
                        $stripeCustomer = $jsonCheck["customer"];
                        $method = $jsonCheck["method"];
                    }
                    try {
                        if ($stripeCustomer) {
                            $stripeCustomer = Stripe\Customer::retrieve($stripeCustomer);
                            if (!$method) {
                                $stripeCustomer->delete();
                            } else {
                                if ($method) {
                                    $method = Stripe\PaymentMethod::retrieve($method);
                                    if ($method->customer) {
                                        $method->detach();
                                    }
                                }
                            }
                            return array("status" => "success", "rawdata" => $stripeCustomer->jsonSerialize());
                        }
                    } catch (Exception $e) {
                        return array("status" => "error", "rawdata" => array("customer" => $stripeCustomer, "error" => $e->getMessage()));
                    }
                }
            }
        }
    }
    return array("status" => "error", "rawdata" => "No Stripe Details Found for Update");
}
function stripe_refund(array $params = array())
{
    $amount = _stripe_formatamount($params["amount"], $params["currency"]);
    stripe_start_stripe($params);
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
function stripe_cc_validation(array $params = array())
{
    if (App::isInRequest("remoteStorageToken")) {
        WHMCS\Session::set("remoteStorageToken", (string) App::getFromRequest("remoteStorageToken"));
    }
    return "";
}
function stripe_credit_card_input(array $params = array())
{
    $existingSubmittedToken = "";
    $assetHelper = DI::make("asset");
    $now = time();
    $token = App::getFromRequest("remoteStorageToken");
    if ($token && substr($token, 0, 2) != "pi") {
        $token = "";
    }
    if ($params["gatewayid"]) {
        $remoteToken = stripe_parseGatewayToken($params["gatewayid"]);
        if ($remoteToken && array_key_exists("method", $remoteToken)) {
            $existingSubmittedToken = $remoteToken["method"];
        }
    } else {
        $userId = (int) WHMCS\Session::get("uid");
        try {
            if ($userId && ($client = WHMCS\User\Client::findOrFail($userId))) {
                $remoteToken = stripe_findFirstCustomerToken($client);
                if ($remoteToken && array_key_exists("method", $remoteToken)) {
                    $existingSubmittedToken = $remoteToken["method"];
                }
            }
        } catch (Exception $e) {
        }
    }
    if ($token) {
        $existingSubmittedToken = $token;
    }
    $additional = "    \n    existingToken = '" . $existingSubmittedToken . "';";
    $amount = 0;
    $currencyCode = "";
    $description = stripe_statement_descriptor($params);
    if (array_key_exists("rawtotal", $params)) {
        $currencyData = getCurrency(WHMCS\Session::get("uid"), WHMCS\Session::get("currency"));
        $description = $description . " " . Lang::trans("carttitle");
        $amount = $params["rawtotal"];
        $currencyCode = $currencyData["code"];
        if (isset($params["convertto"]) && $params["convertto"]) {
            $currencyCode = WHMCS\Database\Capsule::table("tblcurrencies")->where("id", "=", (int) $params["convertto"])->value("code");
            $amount = convertCurrency($amount, $currencyData["id"], $params["convertto"]);
        }
        $amount = _stripe_formatamount($amount, $currencyCode);
    }
    if (array_key_exists("amount", $params)) {
        $amount = $params["amount"];
        $currencyCode = $params["currency"];
        $amount = _stripe_formatamount($amount, $currencyCode);
    }
    $statementDescription = substr($description, 0, 22);
    if ($params["applePay"]) {
        if ($amount) {
            $additional .= "\n    paymentRequestButtonEnabled = true;\n    paymentRequestAmountDue = " . $amount . ";\n    paymentRequestCurrency = '" . $currencyCode . "';\n    paymentRequestDescription = '" . $statementDescription . "';";
        }
    } else {
        $additional .= "\n    paymentRequestButtonEnabled = false;";
    }
    $savePaymentMethod = 0;
    stripe_start_stripe($params);
    if ($amount) {
        $intentsArray = array("description" => $description, "amount" => $amount, "currency" => strtolower($currencyCode), "payment_method_types" => array("card"), "statement_descriptor_suffix" => $statementDescription);
        if ($params["gatewayid"] && substr($params["gatewayid"], 0, 3) == "cus") {
            $intentsArray["customer"] = $params["gatewayid"];
            $savePaymentMethod = 1;
        }
        WHMCS\Session::set("StripeIntentsData", $intentsArray);
    }
    if ($error = WHMCS\Session::getAndDelete("StripeDeclined" . $params["invoiceid"])) {
        $error = Lang::trans("creditcarddeclined");
        $additional .= "\njQuery('.gateway-errors').html('" . $error . "').removeClass('hidden');";
    }
    $additional .= "\n    elementOptions = {\n        style: {\n            base: {\n                \n            }\n        }\n    },\n        card = elements.create('cardNumber', elementOptions),\n        cardExpiryElements = elements.create('cardExpiry', elementOptions),\n        cardCvcElements = elements.create('cardCvc', elementOptions),\n        savePaymentMethod = " . $savePaymentMethod . ";";
    $lang = array("creditCardInput" => addslashes(Lang::trans("creditcardcardnumber")), "creditCardExpiry" => addslashes(Lang::trans("creditcardcardexpires")), "creditCardCvc" => addslashes(Lang::trans("creditcardcvvnumbershort")), "newCardInformation" => addslashes(Lang::trans("creditcardenternewcard")), "or" => addslashes(Lang::trans("or")));
    $apiVersion = WHMCS\Module\Gateway\Stripe\Constant::$apiVersion;
    return "<script type=\"text/javascript\" src=\"" . $assetHelper->getWebRoot() . "/modules/gateways/stripe/stripe.min.js?a=" . $now . "\"></script>\n<script type=\"text/javascript\">\n\nvar card = null, \n    stripe = null, \n    elements = null,\n    lang = null,\n    existingToken = null,\n    paymentRequestButtonEnabled = null,\n    paymentRequestAmountDue = null,\n    paymentRequestCurrency = null,\n    paymentRequestDescription = null,\n    paymentRequestButtonEnabled = null,\n    elementOptions = null,\n    amount = '" . $amount . "',\n    elementsClass = 'form-group';\n\n\$(document).ready(function() {\n    stripe = Stripe('" . $params["publishableKey"] . "');\n    stripe.api_version = \"" . $apiVersion . "\";\n    elements = stripe.elements();\n    " . $additional . "\n    lang = {\n        creditCardInput: '" . $lang["creditCardInput"] . "',\n        creditCardExpiry: '" . $lang["creditCardExpiry"] . "',\n        creditCardCvc: '" . $lang["creditCardCvc"] . "',\n        newCardInformation: '" . $lang["newCardInformation"] . "',\n        or: '" . $lang["or"] . "'\n    };\n    \n    initStripe();\n});    \n</script>\n<link href=\"" . $assetHelper->getWebRoot() . "/modules/gateways/stripe/stripe.css?a=" . $now . "\" rel=\"stylesheet\">";
}
function stripe_statement_descriptor(array $params)
{
    $invoiceNumber = array_key_exists("invoicenum", $params) && $params["invoicenum"] ? $params["invoicenum"] : $params["invoiceid"];
    return substr(str_replace(array("{CompanyName}", "{InvoiceNumber}", ">", "<", "'", "\""), array(WHMCS\Config\Setting::getValue("CompanyName"), $invoiceNumber, "", "", "", ""), $params["statementDescriptor"]), -22);
}
function stripe_start_stripe(array $params)
{
    Stripe\Stripe::setAppInfo(WHMCS\Module\Gateway\Stripe\Constant::$appName, App::getVersion()->getMajor(), WHMCS\Module\Gateway\Stripe\Constant::$appUrl, WHMCS\Module\Gateway\Stripe\Constant::$appPartnerId);
    Stripe\Stripe::setApiKey($params["secretKey"]);
    Stripe\Stripe::setApiVersion(WHMCS\Module\Gateway\Stripe\Constant::$apiVersion);
}
function stripe_parseGatewayToken($data)
{
    $data = json_decode($data, true);
    if ($data && is_array($data)) {
        return $data;
    }
    return array();
}
function stripe_findFirstCustomerToken(WHMCS\User\Contracts\ContactInterface $client)
{
    $clientToUse = $client;
    if ($clientToUse instanceof WHMCS\User\Client\Contact) {
        $clientToUse = $clientToUse->client;
    }
    foreach ($clientToUse->payMethods as $payMethod) {
        if ($payMethod->gateway_name == "stripe") {
            $payment = $payMethod->payment;
            $token = stripe_parsegatewaytoken($payment->getRemoteToken());
            if ($token) {
                return $token;
            }
        }
    }
}
function stripe_get_existing_remote_token(array $params)
{
    $remoteToken = $params["remoteToken"];
    if (substr($remoteToken, 0, 3) === "cus") {
        stripe_start_stripe($params);
        $stripeCustomer = Stripe\Customer::retrieve($remoteToken);
        $source = $stripeCustomer->default_source;
        if ($source) {
            $method = Stripe\PaymentMethod::retrieve($source);
            $remoteToken = json_encode(array("customer" => $stripeCustomer->id, "method" => $method->id));
            $params["payMethod"]->payment->setRemoteToken($remoteToken)->save();
        }
    }
    $remoteToken = stripe_parsegatewaytoken($remoteToken);
    if (count($remoteToken) < 2) {
        throw new InvalidArgumentException("Invalid Remote Token");
    }
    return $remoteToken["method"];
}

?>