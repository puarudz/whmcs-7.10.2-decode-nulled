<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\Paypalcheckout;

class PaypalApi
{
    public function getAccessToken($clientId, $clientSecret, $sandbox)
    {
        $endpoint = "v1/oauth2/token";
        $options = array("CURLOPT_HTTPHEADER" => array("Accept: application/json", "Accept-Language: en_US"), "CURLOPT_USERPWD" => $clientId . ":" . $clientSecret);
        $data = array("grant_type" => "client_credentials");
        return (new ApiClient())->setSandbox($sandbox)->setOptions($options)->post($endpoint, $data)->getFromResponse("access_token");
    }
    public function createOrder($amount, $currency, $intent, $companyName, \WHMCS\User\Client $client = NULL, \WHMCS\Billing\Invoice $invoice = NULL)
    {
        $endpoint = "v2/checkout/orders";
        if ($invoice) {
            $description = $companyName . " - Invoice #" . $invoice->getInvoiceNumber();
            $invoiceId = $invoice->id;
        } else {
            $description = $companyName . " Shopping Cart Checkout";
            $invoiceId = 0;
        }
        $data = array("intent" => $intent, "purchase_units" => array(array("description" => $description, "amount" => array("currency_code" => $currency, "value" => $amount))));
        if (0 < $invoiceId) {
            $data["purchase_units"][0]["invoice_id"] = $invoiceId;
        }
        if ($client instanceof \WHMCS\User\Client) {
            $data["payer"] = array("name" => array("given_name" => $client->firstName, "surname" => $client->lastName), "email_address" => $client->email, "address" => array("address_line_1" => $client->address1, "address_line_2" => $client->address2, "admin_area_1" => $client->state, "admin_area_2" => $client->city, "postal_code" => $client->postcode, "country_code" => $client->country));
        }
        $response = $this->call("post", $endpoint, json_encode($data), true);
        return $response->getFromResponse("id");
    }
    public function captureOrder($orderId)
    {
        $endpoint = "v2/checkout/orders/" . $orderId . "/capture";
        $response = $this->call("post", $endpoint, null, true);
        return $response->getResponse();
    }
    public function activateSubscription($subscriptionId)
    {
        $endpoint = "v1/billing/subscriptions/" . $subscriptionId . "/activate";
        $response = $this->call("post", $endpoint);
        return $response->getResponse();
    }
    public function createProduct($name, $description)
    {
        $endpoint = "v1/catalogs/products";
        $data = array("name" => $name, "description" => $description, "type" => "Service");
        $response = $this->call("post", $endpoint, json_encode($data));
        return $response->getFromResponse("id");
    }
    public function createProductPlan($productId, $productName, $productDescription, $totalDueToday, $recurringAmount, $billingCycle, $billingCyclePeriod, $currencyCode, $initialCycle = NULL, $initialPeriod = NULL)
    {
        $endpoint = "v1/billing/plans";
        $billingCycles = array();
        $sequence = 1;
        if ($totalDueToday != $recurringAmount) {
            if (!is_null($initialCycle) && !is_null($initialPeriod)) {
                $paypalInitialCycle = $this->getPaypalCycle($initialCycle);
            } else {
                $paypalInitialCycle = $this->getPaypalCycle($billingCycle);
                $initialPeriod = $billingCyclePeriod;
            }
            $billingCycles[] = array("frequency" => array("interval_unit" => $paypalInitialCycle, "interval_count" => $initialPeriod), "tenure_type" => "TRIAL", "sequence" => $sequence, "total_cycles" => 1, "pricing_scheme" => array("fixed_price" => array("value" => $totalDueToday, "currency_code" => $currencyCode)));
            $sequence++;
        }
        $billingCycles[] = array("frequency" => array("interval_unit" => $this->getPaypalCycle($billingCycle), "interval_count" => $billingCyclePeriod), "tenure_type" => "REGULAR", "sequence" => $sequence, "total_cycles" => 0, "pricing_scheme" => array("fixed_price" => array("value" => $recurringAmount, "currency_code" => $currencyCode)));
        $data = array("product_id" => $productId, "name" => $productName, "description" => $productDescription, "status" => "ACTIVE", "billing_cycles" => $billingCycles, "payment_preferences" => array("auto_bill_outstanding" => true, "payment_failure_threshold" => "3"));
        $response = $this->call("post", $endpoint, json_encode($data));
        return $response->getFromResponse("id");
    }
    protected function getPaypalCycle($cycle)
    {
        if ($cycle == "days") {
            return "DAY";
        }
        if ($cycle == "monthly") {
            return "MONTH";
        }
        if ($cycle == "annually") {
            return "YEAR";
        }
        return null;
    }
    public function createSubscription($planId, \WHMCS\User\Client $client, $companyName, $returnUrl, $cancelUrl)
    {
        $endpoint = "v1/billing/subscriptions";
        $data = array("plan_id" => $planId, "quantity" => "1", "subscriber" => array("name" => array("given_name" => $client->firstName, "surname" => $client->lastName), "email_address" => $client->email), "application_context" => array("brand_name" => $companyName, "shipping_preference" => "NO_SHIPPING", "user_action" => "SUBSCRIBE_NOW", "payment_method" => array("payer_selected" => "PAYPAL", "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"), "return_url" => $returnUrl, "cancel_url" => $cancelUrl));
        return $this->call("post", $endpoint, json_encode($data), true);
    }
    public function getOrderDetails($orderId)
    {
        $endpoint = "v2/checkout/orders/" . $orderId;
        $response = $this->call("get", $endpoint);
        return $response->getResponse();
    }
    public function getSubscriptionDetails($subscriptionId)
    {
        $endpoint = "v1/billing/subscriptions/" . $subscriptionId;
        return $this->call("get", $endpoint);
    }
    public function refundPayment($invoiceId, $paymentId, $amount, $currencyCode)
    {
        $endpoint = "v2/payments/captures/" . $paymentId . "/refund";
        $data = array("amount" => array("value" => $amount, "currency_code" => $currencyCode), "invoice_id" => $invoiceId);
        return $this->call("post", $endpoint, json_encode($data), true);
    }
    public function cancelSubscription($subscriptionId)
    {
        $endpoint = "v1/billing/subscriptions/" . $subscriptionId . "/cancel";
        return $this->call("post", $endpoint);
    }
    public function getCaptureDetails($captureId)
    {
        $endpoint = "v2/payments/captures/" . $captureId;
        $response = $this->call("get", $endpoint);
        return $response->getResponse();
    }
    public function authorizeOrder($orderId)
    {
        $endpoint = "v2/checkout/orders/" . $orderId . "/authorize";
        $response = $this->call("post", $endpoint);
        $data = $response->getResponse();
        return $data->purchase_units[0]->payments->authorizations[0]->id;
    }
    public function capturePayment($authId, $amount, $currency, $invoiceNumber)
    {
        $endpoint = "v2/payments/authorizations/" . $authId . "/capture";
        $data = array("amount" => array("value" => $amount, "currency_code" => $currency), "invoice_id" => $invoiceNumber, "final_capture" => true);
        $response = $this->call("post", $endpoint, json_encode($data), true);
        return $response->getResponse();
    }
    public function createWebhook($url, $eventTypes)
    {
        $endpoint = "v1/notifications/webhooks";
        $data = array("url" => $url, "event_types" => array());
        foreach ($eventTypes as $eventType) {
            $data["event_types"][] = array("name" => $eventType);
        }
        $response = $this->call("post", $endpoint, json_encode($data));
        return $response->getFromResponse("id");
    }
    public function listWebhooks()
    {
        $endpoint = "v1/notifications/webhooks";
        $response = $this->call("get", $endpoint);
        return $response->getResponse();
    }
    public function verifyWebhookSignature($authAlgo, $certUrl, $transmissionId, $transmissionSig, $transmissionTime, $webhookId, $webhookEvent)
    {
        $endpoint = "v1/notifications/verify-webhook-signature";
        $data = array("auth_algo" => $authAlgo, "cert_url" => $certUrl, "transmission_id" => $transmissionId, "transmission_sig" => $transmissionSig, "transmission_time" => $transmissionTime, "webhook_id" => $webhookId, "webhook_event" => $webhookEvent);
        $response = $this->call("post", $endpoint, json_encode($data));
        return $response->getFromResponse("verification_status") === "SUCCESS";
    }
    protected function call($method, $endpoint, $data = NULL, $sendPartnerId = false)
    {
        $settings = \WHMCS\Module\GatewaySetting::getForGateway("paypalcheckout");
        $accessTokenName = "accessToken-" . md5($settings["clientId"]);
        $accessToken = isset($settings[$accessTokenName]) ? decrypt($settings[$accessTokenName]) : null;
        if (empty($accessToken)) {
            $accessToken = $this->getAccessToken($settings["clientId"], $settings["clientSecret"], $settings["sandbox"]);
            \WHMCS\Module\GatewaySetting::setValue("paypalcheckout", $accessTokenName, encrypt($accessToken));
        }
        try {
            return (new ApiClient())->setSandbox($settings["sandbox"])->setAccessToken($accessToken)->setSendPartnerId($sendPartnerId)->{$method}($endpoint, $data);
        } catch (Exception\AuthError $e) {
            $accessToken = $this->getAccessToken($settings["clientId"], $settings["clientSecret"], $settings["sandbox"]);
            \WHMCS\Module\GatewaySetting::setValue("paypalcheckout", $accessTokenName, encrypt($accessToken));
            return (new ApiClient())->setSandbox($settings["sandbox"])->setAccessToken($accessToken)->setSendPartnerId($sendPartnerId)->{$method}($endpoint, $data);
        }
    }
}

?>