<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("paypalcheckout");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$bodyReceived = $request->getContent();
$decoded = array();
try {
    if (empty($bodyReceived)) {
        throw new Exception("No data received");
    }
    $decoded = json_decode($bodyReceived, true);
    if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid data received");
    }
    if (!array_key_exists("resource_type", $decoded)) {
        throw new Exception("Resource type missing");
    }
    if (!array_key_exists("event_type", $decoded)) {
        throw new Exception("Event type missing");
    }
    if (!array_key_exists("event_version", $decoded)) {
        throw new Exception("Event version missing");
    }
    $resourceType = $decoded["resource_type"];
    $eventType = $decoded["event_type"];
    $eventVersion = $decoded["event_version"];
    $headers = array_change_key_case($request->headers->all(), CASE_UPPER);
    if (!array_key_exists("PAYPAL-TRANSMISSION-SIG", $headers) || !array_key_exists("PAYPAL-AUTH-ALGO", $headers) || !array_key_exists("PAYPAL-CERT-URL", $headers)) {
        throw new Exception("Signature data missing");
    }
    $transSig = $headers["PAYPAL-TRANSMISSION-SIG"][0];
    $transTime = $headers["PAYPAL-TRANSMISSION-TIME"][0];
    $transId = $headers["PAYPAL-TRANSMISSION-ID"][0];
    $authAlgo = $headers["PAYPAL-AUTH-ALGO"][0];
    $certUrl = $headers["PAYPAL-CERT-URL"][0];
    $webhookId = WHMCS\Config\Setting::getValue("PayPalCheckoutWebhookId");
    $valid = (new WHMCS\Module\Gateway\Paypalcheckout\PaypalApi())->verifyWebhookSignature($authAlgo, $certUrl, $transId, $transSig, $transTime, $webhookId, $decoded);
    if ($valid) {
        try {
            $webhookHandler = new WHMCS\Module\Gateway\Paypalcheckout\PayPalWebhookHandler();
            $responseMsg = $webhookHandler->execute($decoded);
            logTransaction("PayPal Webhook", $decoded, $responseMsg);
        } catch (Exception $e) {
            logTransaction("PayPal Webhook", $decoded, $e->getMessage());
        }
    } else {
        throw new Exception("Signature Verification Failed");
    }
} catch (Exception $e) {
    $dataToLog = $decoded;
    if (empty($dataToLog)) {
        $dataToLog = $bodyReceived;
    }
    logTransaction("PayPal Webhook", $dataToLog, $e->getMessage());
    header("HTTP/1.0 406 Not Acceptable");
    exit;
}

?>