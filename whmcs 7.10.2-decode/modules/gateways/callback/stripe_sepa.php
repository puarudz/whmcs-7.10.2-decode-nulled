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
App::load_function("gateway");
App::load_function("invoice");
try {
    $gatewayParams = getGatewayVariables("stripe_sepa");
    if (!$gatewayParams["type"]) {
        throw new WHMCS\Payment\Exception\InvalidModuleException("Module Not Activated");
    }
    $passedParams = array();
    $payload = @file_get_contents("php://input");
    $sigHeader = $_SERVER["HTTP_STRIPE_SIGNATURE"];
    $event = NULL;
    stripe_sepa_start_stripe($gatewayParams);
    $event = Stripe\Webhook::constructEvent($payload, $sigHeader, $gatewayParams["webhookEndpointSecret"]);
    switch ($event->type) {
        case "charge.succeeded":
            $charge = $event->data->object;
            $chargeMetaData = json_decode(json_encode($charge->metadata), true);
            $webhookProtection = substr(sha1(App::getLicense()->getLicenseKey()), 0, 16);
            if (!isset($chargeMetaData["webhookProtection"])) {
                throw new Stripe\Error\SignatureVerification("Invalid Installation", $sigHeader);
            }
            if ($chargeMetaData["webhookProtection"] !== $webhookProtection) {
                throw new WHMCS\Exception\Module\NotServicable("Webhook Protection Validation Failed");
            }
            $transaction = Stripe\BalanceTransaction::retrieve($charge->balance_transaction);
            $transactionExchangeRate = $transaction->exchange_rate;
            $conversionCurrency = WHMCS\Billing\Currency::where("code", strtoupper($charge->currency))->first();
            $transactionId = $transaction->id;
            checkCbTransID($transactionId);
            $invoiceId = $charge->metadata->id;
            try {
                $invoice = WHMCS\Billing\Invoice::with("client")->findOrFail($invoiceId);
                $transactionFee = $transaction->fee / 100;
                $amount = $transaction->amount / 100;
                if ($transactionExchangeRate) {
                    $transactionFee /= $transactionExchangeRate;
                    $amount /= $transactionExchangeRate;
                    $convertCurrency = $params["convertto"];
                    if (!$convertCurrency) {
                        $convertCurrency = $invoice->client->currencyId;
                    }
                    if ($convertCurrency && $conversionCurrency) {
                        $transactionFee = convertCurrency($transactionFee, $conversionCurrency->id, $convertCurrency);
                        $amount = convertCurrency($amount, $conversionCurrency->id, $convertCurrency);
                    }
                }
                $history = WHMCS\Billing\Payment\Transaction\History::firstOrNew(array("invoice_id" => $invoice->id, "gateway" => $gatewayParams["paymentmethod"], "transaction_id" => $transactionId));
                $history->remoteStatus = $charge->status;
                $history->description = "Payment Confirmed";
                $history->additionalInformation = $charge->jsonSerialize();
                $history->completed = true;
                $history->save();
                $passedParams["history_id"] = $history->id;
                checkCbTransID($transactionId);
                $invoice->addPayment($amount, $transactionId, $transactionFee, $gatewayParams["paymentmethod"]);
                $data = array("charge" => $charge->jsonSerialize(), "transaction" => $transaction->jsonSerialize());
                $logTransactionResult = "Success";
            } catch (Exception $e) {
                $data = array("message" => "Invalid Invoice ID", "invoiceIdReturned" => $invoiceId, "event" => $event->jsonSerialize(), "charge" => $charge->jsonSerialize(), "transaction" => $transaction->jsonSerialize());
                $logTransactionResult = "Error";
            }
            break;
        case "charge.failed":
            $charge = $event->data->object;
            $invoiceId = $charge->metadata->id;
            $transaction = Stripe\BalanceTransaction::retrieve($charge->balance_transaction);
            try {
                $invoice = WHMCS\Billing\Invoice::findOrFail($invoiceId);
                $history = WHMCS\Billing\Payment\Transaction\History::firstOrNew(array("invoice_id" => $invoice->id, "gateway" => $gatewayParams["paymentmethod"], "transaction_id" => $transaction->id));
                $history->remoteStatus = $charge->status;
                $history->description = $charge->failure_message;
                $history->additionalInformation = $charge->jsonSerialize();
                $history->completed = false;
                $history->save();
                $passedParams["history_id"] = $history->id;
                $invoice->status = "Unpaid";
                $invoice->save();
                $emailTemplate = "Credit Card Payment Failed";
                $gateway = WHMCS\Module\Gateway::factory($gatewayParams["paymentmethod"]);
                if ($customEmailTemplate = $gateway->getMetaDataValue("failedEmail")) {
                    $customEmailTemplate = WHMCS\Mail\Template::where("name", "=", $customEmailTemplate)->first();
                    if ($customEmailTemplate) {
                        $emailTemplate = $customEmailTemplate->name;
                    }
                }
                sendMessage($emailTemplate, $invoiceId);
                $data = array("event" => $event->jsonSerialize(), "charge" => $charge->jsonSerialize(), "transaction" => $transaction->jsonSerialize());
                $logTransactionResult = "Payment Failed";
            } catch (Exception $e) {
                $data = array("message" => "Invalid Invoice ID", "invoiceIdReturned" => $invoiceId, "event" => $event->jsonSerialize(), "charge" => $charge->jsonSerialize(), "transaction" => $transaction->jsonSerialize());
                $logTransactionResult = "Error";
            }
            break;
        default:
            WHMCS\Terminus::getInstance()->doExit();
    }
} catch (WHMCS\Payment\Exception\InvalidModuleException $e) {
    $gatewayParams["paymentmethod"] = "stripe_sepa";
    $data = array("error" => $e->getMessage());
    $logTransactionResult = "Module Not Active";
} catch (WHMCS\Exception\Module\NotServicable $e) {
    $gatewayParams["paymentmethod"] = "stripe_sepa";
    $data = array("payload" => $payload, "error" => $e->getMessage(), "webhookProtectionValue" => $webhookProtection);
    $logTransactionResult = "Invalid Webhook Protection";
} catch (Stripe\Error\SignatureVerification $e) {
    $gatewayInterface = new WHMCS\Module\Gateway();
    if ($gatewayInterface->isActiveGateway("stripe_ach")) {
        WHMCS\Terminus::getInstance()->doExit();
    }
    $data = array("payload" => $payload, "error" => "Invalid Access Attempt");
    $logTransactionResult = "Invalid Access Attempt";
} catch (Exception $e) {
    $data = array("payload" => $payload, "error" => $e->getMessage());
    $logTransactionResult = "Invalid Response";
    http_response_code(400);
}
logTransaction($gatewayParams["paymentmethod"], $data, $logTransactionResult, $passedParams);
WHMCS\Terminus::getInstance()->doExit();

?>