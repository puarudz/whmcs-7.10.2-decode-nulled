<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\StripeAch;

class StripeAchController
{
    public function exchange(\WHMCS\Http\Message\ServerRequest $request)
    {
        $publicToken = $request->get("public_token");
        $accountId = $request->get("account_id");
        try {
            $gateway = \WHMCS\Module\Gateway::factory("stripe_ach");
        } catch (\Exception $e) {
            return new \WHMCS\Http\Message\JsonResponse(array("warning" => \Lang::trans("errors.badRequest")));
        }
        $gatewayParams = $gateway->getParams();
        $prefix = $gatewayParams["plaidMode"];
        $plaidUrl = "https://" . $prefix . ".plaid.com/";
        $exchange = "item/public_token/exchange";
        $bankToken = "processor/stripe/bank_account_token/create";
        $client = new \GuzzleHttp\Client(array("http_errors" => false));
        $response = $client->post($plaidUrl . $exchange, array("headers" => array("Content-Type" => "application/json"), "json" => array("client_id" => $gatewayParams["plaidClientId"], "secret" => $gatewayParams["plaidSecret"], "public_token" => $publicToken)));
        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody());
        if ($statusCode < 400) {
            $accessToken = $data->access_token;
            $response = $client->post($plaidUrl . $bankToken, array("headers" => array("Content-Type" => "application/json"), "json" => array("client_id" => $gatewayParams["plaidClientId"], "secret" => $gatewayParams["plaidSecret"], "access_token" => $accessToken, "account_id" => $accountId)));
            $statusCode = $response->getStatusCode();
            $data = json_decode($response->getBody());
        }
        if ($statusCode < 400) {
            return new \WHMCS\Http\Message\JsonResponse(array("token" => $data->stripe_bank_account_token));
        }
        $error = array("Malformed response received from server. Please contact support.");
        if ($data !== null && 400 <= $statusCode) {
            $error = array();
            foreach ($data->fields as $field) {
                $error[] = $field->path . " " . $field->message;
            }
        }
        return new \WHMCS\Http\Message\JsonResponse(array("warning" => implode("<br>", $error)));
    }
}

?>