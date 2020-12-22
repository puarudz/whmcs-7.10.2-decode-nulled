<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\Stripe;

class StripeController
{
    public function intent(\WHMCS\Http\Message\ServerRequest $request)
    {
        $token = $request->get("token");
        check_token("WHMCS.default", $token);
        $paymentMethodId = $request->get("payment_method_id");
        if (!function_exists("checkDetailsareValid")) {
            require_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "clientfunctions.php";
        }
        $gateway = new \WHMCS\Module\Gateway();
        if (!$gateway->load("stripe")) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Module Not Active"));
        }
        $gatewayParams = $gateway->getParams();
        stripe_start_stripe($gatewayParams);
        $invoiceId = $request->get("invoiceid");
        $stripeCustomer = null;
        $client = null;
        $method = null;
        $billingContact = null;
        if ($paymentMethodId) {
            try {
                $method = \Stripe\PaymentMethod::retrieve($paymentMethodId);
                if ($method->customer) {
                    $stripeCustomer = \Stripe\Customer::retrieve($method->customer);
                }
            } catch (\Exception $e) {
            }
        }
        $clientId = null;
        if (!$stripeCustomer && $invoiceId) {
            $invoice = \WHMCS\Billing\Invoice::with("client")->find($invoiceId);
            $sessionUser = \WHMCS\Session::get("uid");
            if ($sessionUser != $invoice->clientId) {
                throw new \InvalidArgumentException("Invalid Access Attempt");
            }
            $client = $invoice->client;
            $clientId = $client->id;
        }
        $errorMessage = null;
        if (!$client) {
            $clientId = \WHMCS\Session::get("uid");
        }
        if (!$clientId) {
            $newOrExisting = $request->get("custtype");
            if ($newOrExisting === "existing") {
                $loginEmail = $request->get("loginemail");
                $loginPw = \WHMCS\Input\Sanitize::decode($request->get("loginpw"));
                if (!$loginPw) {
                    $loginPw = \WHMCS\Input\Sanitize::decode($request->get("loginpassword"));
                }
                $loginCheck = localAPI("validatelogin", array("email" => $loginEmail, "password2" => $loginPw));
                if ($loginCheck["result"] === "success") {
                    if ($loginCheck["twoFactorEnabled"] === true) {
                        $response = array("two_factor" => true);
                        return new \WHMCS\Http\Message\JsonResponse($response);
                    }
                    $clientId = (int) $loginCheck["userid"];
                } else {
                    $response = array("warning" => \Lang::trans("loginincorrect"));
                    return new \WHMCS\Http\Message\JsonResponse($response);
                }
            } else {
                $whmcs = \App::self();
                $errorMessage = checkDetailsareValid("", true, true, false);
            }
        }
        if ($clientId) {
            if (!$client) {
                $client = \WHMCS\User\Client::find($clientId);
            }
            if (\App::isInRequest("billingcontact")) {
                $billingContactId = \App::getFromRequest("billingcontact");
                if ($billingContactId === "new") {
                    $errorMessage = checkDetailsareValid($clientId, false, false, false, false);
                }
            }
        }
        if ($request->has("custtype")) {
            if (!function_exists("cartValidationOnCheckout")) {
                require_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "cartfunctions.php";
            }
            $errorMessage .= cartValidationOnCheckout($clientId, true);
        }
        if ($errorMessage) {
            $response = array("warning" => $errorMessage, "reloadCaptcha" => (bool) (!\WHMCS\Session::get("CartValidationOnCheckout")));
            return new \WHMCS\Http\Message\JsonResponse($response);
        }
        if ($client && !$stripeCustomer) {
            $gatewayId = json_encode(stripe_findFirstCustomerToken($client));
            $clientId = $client->id;
            if ($client instanceof \WHMCS\User\Client\Contact) {
                $clientId = $client->clientId;
            }
            if ($client->billingContactId) {
                $billingContact = $client->billingContact;
            }
            if (\App::isInRequest("billingcontact")) {
                $billingContactId = \App::getFromRequest("billingcontact");
                if ($billingContactId == "new") {
                    $billingContact = new \WHMCS\User\Client\Contact();
                    $billingContact->clientId = $clientId;
                    $billingContact->firstName = \App::getFromRequest("firstname");
                    $billingContact->lastName = \App::getFromRequest("lastname");
                    $billingContact->email = $client->email;
                    $billingContact->address1 = \App::getFromRequest("address1");
                    $billingContact->address2 = \App::getFromRequest("address2");
                    $billingContact->city = \App::getFromRequest("city");
                    $billingContact->state = \App::getFromRequest("state");
                    $billingContact->postcode = \App::getFromRequest("postcode");
                    $billingContact->country = \App::getFromRequest("country");
                } else {
                    $billingContact = $client->contacts()->where("id", $billingContactId)->first();
                }
            }
            if ($gatewayId) {
                $jsonCheck = json_decode(\WHMCS\Input\Sanitize::decode($gatewayId), true);
                if (is_array($jsonCheck) && array_key_exists("customer", $jsonCheck)) {
                    $stripeCustomer = \Stripe\Customer::retrieve($jsonCheck["customer"]);
                    if (!$paymentMethodId) {
                        $paymentMethodId = $jsonCheck["method"];
                    }
                } else {
                    if (substr($gatewayId, 0, 3) == "cus") {
                        $stripeCustomer = \Stripe\Customer::retrieve($gatewayId);
                    }
                }
            }
            try {
                $method = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            } catch (\Exception $e) {
                return new \WHMCS\Http\Message\JsonResponse(array("warning" => $e->getMessage()));
            }
        }
        if (!$billingContact) {
            $localPayMethodId = \App::getFromRequest("ccinfo");
            if (is_numeric($localPayMethodId)) {
                $payMethod = $client->payMethods()->where("id", $localPayMethodId)->first();
                if ($payMethod) {
                    $billingContact = $payMethod->contact;
                }
            }
        }
        if (!$stripeCustomer && $client) {
            $stripeCustomer = \Stripe\Customer::create(array("description" => "Customer for " . $client->fullName . " (" . $client->email . ")", "email" => $client->email, "metadata" => array("id" => $clientId, "fullName" => $client->fullName, "email" => $client->email)));
        } else {
            if (!$stripeCustomer && !$client) {
                $name = \App::getFromRequest("firstname") . " " . \App::getFromRequest("lastname");
                $email = \App::getFromRequest("email");
                if (!trim($name) || !$email) {
                    $response = array("warning" => "Name and Email are required to pay with this gateway");
                    return new \WHMCS\Http\Message\JsonResponse($response);
                }
                $stripeCustomer = \Stripe\Customer::create(array("description" => "Customer for " . $name . " (" . $client->email . ")", "email" => $email, "metadata" => array("fullName" => $name, "email" => $email)));
            }
        }
        if (!$method->customer) {
            try {
                $method = $method->attach(array("customer" => $stripeCustomer->id));
                $method->save();
            } catch (\Exception $e) {
                $response = array("warning" => $e->getMessage());
                return new \WHMCS\Http\Message\JsonResponse($response);
            }
        }
        $methodId = $method->id;
        if (substr($methodId, 0, 4) !== "card") {
            if ($client) {
                if (!$billingContact) {
                    $billingContact = $client;
                }
                $billingContactEmail = $billingContact->email;
                if (!$billingContactEmail) {
                    $billingContactEmail = $client->email;
                }
                $method = \Stripe\PaymentMethod::update($method->id, array("billing_details" => array("email" => $billingContactEmail, "name" => $billingContact->fullName, "address" => array("line1" => _stripe_formatValue($billingContact->address1), "line2" => _stripe_formatValue($billingContact->address2), "city" => _stripe_formatValue($billingContact->city), "state" => _stripe_formatValue($billingContact->state), "country" => _stripe_formatValue($billingContact->country), "postal_code" => _stripe_formatValue($billingContact->postcode))), "metadata" => array("id" => $clientId, "fullName" => $client->fullName, "email" => $client->email)));
            } else {
                $method = \Stripe\PaymentMethod::update($method->id, array("billing_details" => array("email" => $email, "name" => $name, "address" => array("line1" => _stripe_formatValue(\App::getFromRequest("address1")), "line2" => _stripe_formatValue(\App::getFromRequest("address2")), "city" => _stripe_formatValue(\App::getFromRequest("city")), "state" => _stripe_formatValue(\App::getFromRequest("state")), "country" => _stripe_formatValue(\App::getFromRequest("country")), "postal_code" => _stripe_formatValue(\App::getFromRequest("postcode")))), "metadata" => array("fullName" => $name, "email" => $email)));
            }
        }
        try {
            $cartData = array();
            if (!\WHMCS\Session::get("uid")) {
                if (!function_exists("calcCartTotals")) {
                    require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "orderfunctions.php";
                }
                if (!$clientId) {
                    $_SESSION["cart"]["user"]["state"] = $request->get("state");
                    $_SESSION["cart"]["user"]["country"] = $request->get("country");
                }
                $removeSession = false;
                if ($clientId) {
                    \WHMCS\Session::set("uid", $clientId);
                    $removeSession = true;
                }
                global $currency;
                $currency = getCurrency(\WHMCS\Session::get("uid"), \WHMCS\Session::get("currency"));
                $cartData = calcCartTotals(false, false, $currency);
                if ($removeSession) {
                    \WHMCS\Session::delete("uid");
                }
            }
            $intentsData = \WHMCS\Session::get("StripeIntentsData");
            if (!is_array($intentsData)) {
                throw new \InvalidArgumentException("Invalid or Missing Payment Information - Please Reload and Try Again");
            }
            if (array_key_exists("rawtotal", $cartData)) {
                if (empty($currency)) {
                    $currencyData = getCurrency(\WHMCS\Session::get("uid"), \WHMCS\Session::get("currency"));
                } else {
                    $currencyData = $currency;
                }
                $amount = $cartData["rawtotal"];
                $currencyCode = $currencyData["code"];
                if (isset($gatewayParams["convertto"]) && $gatewayParams["convertto"]) {
                    $currencyCode = \WHMCS\Database\Capsule::table("tblcurrencies")->where("id", "=", (int) $gatewayParams["convertto"])->value("code");
                    $amount = convertCurrency($amount, $currencyData["id"], $gatewayParams["convertto"]);
                }
                $amount = _stripe_formatAmount($amount, $currencyCode);
                $intentsData["amount"] = $amount;
                $intentsData["currency"] = strtolower($currencyCode);
            }
            $intentsData["confirmation_method"] = "automatic";
            $intentsData["capture_method"] = "manual";
            $intentsData["confirm"] = true;
            $intentsData["customer"] = $stripeCustomer->id;
            $intentsData["payment_method"] = $method->id;
            $intentsData["save_payment_method"] = true;
            $intentsData["setup_future_usage"] = "off_session";
            $intent = \Stripe\PaymentIntent::create($intentsData);
        } catch (\Exception $e) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => $e->getMessage()));
        }
        switch ($intent->status) {
            case "requires_source_action":
            case "requires_action":
                $response = array("requires_action" => true, "success" => false, "token" => $intent->client_secret);
                break;
            case "requires_capture":
            case "succeeded":
                $response = array("success" => true, "requires_action" => false, "token" => $intent->id);
                break;
            default:
                $response = array("warning" => "Invalid PaymentIntent status");
        }
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
    public function setupIntent(\WHMCS\Http\Message\ServerRequest $request)
    {
        $token = $request->get("token");
        check_token("WHMCS.default", $token);
        $gateway = new \WHMCS\Module\Gateway();
        if (!$gateway->load("stripe")) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Module Not Active"));
        }
        stripe_start_stripe($gateway->getParams());
        $setupIntent = \Stripe\SetupIntent::create();
        return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "setup_intent" => $setupIntent->client_secret));
    }
    public function add(\WHMCS\Http\Message\ServerRequest $request)
    {
        $token = $request->get("token");
        check_token("WHMCS.default", $token);
        return $this->addProcess($request, true);
    }
    public function adminAdd(\WHMCS\Http\Message\ServerRequest $request)
    {
        return $this->addProcess($request);
    }
    protected function addProcess(\WHMCS\Http\Message\ServerRequest $request, $sessionUserId = false)
    {
        $paymentMethodId = $request->get("payment_method_id");
        $userId = (int) $request->get("user_id");
        if ($sessionUserId) {
            $userId = \WHMCS\Session::get("uid");
        }
        if (!$userId) {
            $error = "User Id not found in request params";
            if ($sessionUserId) {
                $error = "Login session not found";
            }
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => $error));
        }
        $gateway = new \WHMCS\Module\Gateway();
        if (!$gateway->load("stripe")) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => "Module Not Active"));
        }
        stripe_start_stripe($gateway->getParams());
        try {
            $client = \WHMCS\User\Client::findOrFail($userId);
            $existingMethod = stripe_findFirstCustomerToken($client);
            $stripeCustomer = null;
            $gatewayId = $client->paymentGatewayToken;
            $billingContactId = \App::getFromRequest("billingcontact");
            $billingContact = null;
            if ($billingContactId) {
                $billingContact = $client->contacts()->where("id", $billingContactId)->first();
            }
            if (!$billingContact) {
                $billingContact = $client;
            }
            if ($gatewayId) {
                $jsonCheck = json_decode(\WHMCS\Input\Sanitize::decode($gatewayId), true);
                if (is_array($jsonCheck) && array_key_exists("customer", $jsonCheck)) {
                    $stripeCustomer = \Stripe\Customer::retrieve($jsonCheck["customer"]);
                } else {
                    if (substr($gatewayId, 0, 3) == "cus") {
                        $stripeCustomer = \Stripe\Customer::retrieve($gatewayId);
                    }
                }
            }
            if (!$stripeCustomer && $existingMethod && is_array($existingMethod) && array_key_exists("customer", $existingMethod)) {
                $stripeCustomer = \Stripe\Customer::retrieve($existingMethod["customer"]);
            }
            if (!$stripeCustomer) {
                $stripeCustomer = \Stripe\Customer::create(array("description" => "Customer for " . $client->fullName . " (" . $client->email . ")", "email" => $client->email, "metadata" => array("id" => $userId, "fullName" => $client->fullName, "email" => $client->email)));
            }
            $method = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            if (!$method->customer) {
                $method->attach(array("customer" => $stripeCustomer->id));
            }
            $billingContactEmail = $billingContact->email;
            if (!$billingContactEmail) {
                $billingContactEmail = $client->email;
            }
            $method = \Stripe\PaymentMethod::update($method->id, array("billing_details" => array("email" => $billingContactEmail, "name" => $billingContact->fullName, "address" => array("line1" => _stripe_formatValue($billingContact->address1), "line2" => _stripe_formatValue($billingContact->address2), "city" => _stripe_formatValue($billingContact->city), "state" => _stripe_formatValue($billingContact->state), "country" => _stripe_formatValue($billingContact->country), "postal_code" => _stripe_formatValue($billingContact->postcode))), "metadata" => array("id" => $userId, "fullName" => $client->fullName, "email" => $client->email)));
            $response = array("success" => true, "requires_action" => false, "token" => $method->id);
        } catch (\Exception $e) {
            $response = array("warning" => $e->getMessage());
        }
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
}

?>