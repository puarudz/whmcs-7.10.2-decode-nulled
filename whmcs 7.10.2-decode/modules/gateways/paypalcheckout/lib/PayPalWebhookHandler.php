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

class PayPalWebhookHandler
{
    protected $actionMap = array("PAYMENT.CAPTURE.PENDING" => "paymentCapturePending", "BILLING.SUBSCRIPTION.CREATED" => "subscriptionCreated", "PAYMENT.SALE.COMPLETED" => "paymentCompleted", "BILLING.SUBSCRIPTION.SUSPENDED" => "subscriptionSuspended", "BILLING.SUBSCRIPTION.CANCELLED" => "subscriptionCancelled", "CUSTOMER.DISPUTE.RESOLVED" => "disputeResolved");
    public function execute($data)
    {
        $eventType = $data["event_type"];
        $methodName = array_key_exists($eventType, $this->actionMap) ? $this->actionMap[$eventType] : null;
        if ($methodName && method_exists($this, $methodName)) {
            return $this->{$methodName}($data);
        }
        return "Information Only";
    }
    protected function paymentCapturePending($data)
    {
        $payment = $data["resource"];
        $transactionId = $payment["id"];
        $amount = $payment["amount"];
        $invoiceId = $payment["invoice_id"];
        $paymentStatus = $payment["status"];
        $statusReason = $payment["status_details"]["reason"];
        $history = new \WHMCS\Billing\Payment\Transaction\History();
        $history->invoice_id = $invoiceId;
        $history->gateway = "paypalcheckout";
        $history->transactionId = $transactionId;
        $history->remoteStatus = $paymentStatus;
        $history->description = $statusReason;
        $history->completed = false;
        $history->save();
        return "Payment Pending";
    }
    protected function subscriptionCreated($data)
    {
        $subscription = $data["resource"];
        $subId = $subscription["id"];
        $firstUnpaidInvoice = \WHMCS\Billing\Invoice::unpaidOrPaymentPending()->subscriptionId($subId)->orderBy("duedate")->first();
        if (!$firstUnpaidInvoice) {
            throw new \WHMCS\Exception("Subscription Created: No invoice found");
        }
        $history = \WHMCS\Billing\Payment\Transaction\History::firstOrNew(array("invoice_id" => $firstUnpaidInvoice->id, "gateway" => "paypalcheckout", "transaction_id" => $subId));
        $history->remoteStatus = $data["summary"];
        $history->description = "";
        $history->completed = true;
        $history->save();
        return "Subscription Created";
    }
    protected function paymentCompleted($data)
    {
        $payment = $data["resource"];
        $transactionId = $payment["id"];
        $transactionState = $payment["state"];
        $amount = $payment["amount"];
        $total = $amount["total"];
        $currency = $amount["currency"];
        $transactionFee = $payment["transaction_fee"];
        $feeAmount = $transactionFee["value"];
        $feeCurrency = $transactionFee["currency"];
        $invoiceNumber = $payment["invoice_number"];
        $billingAgreementId = $payment["billing_agreement_id"];
        if (!$billingAgreementId) {
            return "Information Only";
        }
        $firstUnpaidInvoice = \WHMCS\Billing\Invoice::unpaidOrPaymentPending()->subscriptionId($billingAgreementId)->orderBy("duedate")->first();
        if (!$firstUnpaidInvoice) {
            $clientIdForCredit = 0;
            $service = \WHMCS\Service\Service::where("subscriptionid", $billingAgreementId)->first();
            if (!is_null($service)) {
                $clientIdForCredit = $service->userId;
            }
            if (!$clientIdForCredit) {
                $domain = \WHMCS\Domain\Domain::where("subscriptionid", $billingAgreementId)->first();
                if (!is_null($domain)) {
                    $clientIdForCredit = $domain->userId;
                }
            }
            if (!$clientIdForCredit) {
                throw new \WHMCS\Exception("Subscription Payment: No invoice found");
            }
        }
        if ($firstUnpaidInvoice) {
            if (!trim($currency) || $currency != $firstUnpaidInvoice->getCurrency()["code"]) {
                throw new \WHMCS\Exception("Subscription Payment: Invalid currency");
            }
            try {
                $firstUnpaidInvoice->addPaymentIfNotExists($total, $transactionId, $feeAmount, "paypalcheckout");
                return "Subscription Payment: Success";
            } catch (\Exception $e) {
                throw new \WHMCS\Exception("Subscription Payment: Transaction ID already exists");
            }
        } else {
            if ($clientIdForCredit) {
                $client = \WHMCS\User\Client::find($clientIdForCredit);
                if (!trim($currency) || $currency != $client->currencyrel->code) {
                    throw new \WHMCS\Exception("Subscription Payment: Invalid currency");
                }
                $existingTransaction = \WHMCS\Billing\Payment\Transaction::where("transid", $transactionId)->first();
                if (!is_null($existingTransaction)) {
                    throw new \WHMCS\Exception("Subscription Payment: Transaction ID already exists");
                }
                $transaction = new \WHMCS\Billing\Payment\Transaction();
                $transaction->clientId = $client->id;
                $transaction->currency = $client->currencyrel->id;
                $transaction->gateway = "paypalcheckout";
                $transaction->date = \WHMCS\Carbon::now();
                $transaction->description = "PayPal Subscription Payment";
                $transaction->amountIn = $total;
                $transaction->fees = $feeAmount;
                $transaction->transactionId = $transactionId;
                $transaction->save();
                $client->addCredit("PayPal Subscription Transaction ID " . $transactionId, $total);
                return "Subscription Payment: Credited";
            }
        }
    }
    protected function subscriptionSuspended($data)
    {
        $subscription = $data["resource"];
        $subId = $subscription["id"];
        $invoice = \WHMCS\Billing\Invoice::unpaidOrPaymentPending()->subscriptionId($subId)->orderBy("duedate")->first();
        if (!$invoice) {
            $invoice = \WHMCS\Billing\Invoice::subscriptionId($subId)->orderBy("duedate", "desc")->first();
        }
        if (!$invoice) {
            throw new \WHMCS\Exception("Subscription Suspended: No invoice found");
        }
        $history = new \WHMCS\Billing\Payment\Transaction\History();
        $history->invoice_id = $invoice->id;
        $history->gateway = "paypalcheckout";
        $history->transactionId = $subId;
        $history->remoteStatus = "Subscription Suspended";
        $history->description = "Subscription reached the maximum number of failed retry attempts";
        $history->completed = true;
        $history->save();
        return "Subscription Suspended: Ok";
    }
    protected function subscriptionCancelled($data)
    {
        $subscription = $data["resource"];
        $subId = $subscription["id"];
        $invoice = \WHMCS\Billing\Invoice::unpaidOrPaymentPending()->subscriptionId($subId)->orderBy("duedate")->first();
        if (!$invoice) {
            $invoice = \WHMCS\Billing\Invoice::subscriptionId($subId)->orderBy("duedate", "desc")->first();
        }
        if ($invoice) {
            $history = new \WHMCS\Billing\Payment\Transaction\History();
            $history->invoice_id = $invoice->id;
            $history->gateway = "paypalcheckout";
            $history->transactionId = $subId;
            $history->remoteStatus = "Subscription Cancelled";
            $history->description = "";
            $history->completed = true;
            $history->save();
        }
        foreach (\WHMCS\Service\Service::where("subscriptionid", $subId)->get() as $service) {
            $service->subscriptionId = "";
            $service->save();
            logActivity("PayPal Subscription Cancellation Auto Removal of Subscription ID" . " - Service ID: " . $service->id, $service->userId);
        }
        foreach (\WHMCS\Service\Addon::where("subscriptionid", $subId)->get() as $addon) {
            $addon->subscriptionId = "";
            $addon->save();
            logActivity("PayPal Subscription Cancellation Auto Removal of Subscription ID" . " - Service Addon ID: " . $addon->id, $addon->userId);
        }
        foreach (\WHMCS\Domain\Domain::where("subscriptionid", $subId)->get() as $domain) {
            $domain->subscriptionId = "";
            $domain->save();
            logActivity("PayPal Subscription Cancellation Auto Removal of Subscription ID" . " - Domain ID: " . $domain->id, $domain->userId);
        }
        return "Subscription Cancelled";
    }
    protected function disputeResolved($data)
    {
        $dispute = $data["resource"];
        $disputeId = $dispute["dispute_id"];
        $transactions = $dispute["disputed_transactions"];
        $reason = $dispute["reason"];
        $status = $dispute["status"];
        $disputeOutcome = $dispute["dispute_outcome"];
        $disputeAmount = $dispute["dispute_amount"];
        $disputeLifeCycleStage = $dispute["dispute_life_cycle_stage"];
        $disputeChannel = $dispute["dispute_channel"];
        if ($status == "RESOLVED") {
            $disputeOutcomeCode = $disputeOutcome["outcome_code"];
            if ($disputeOutcomeCode == "RESOLVED_BUYER_FAVOUR") {
                foreach ($transactions as $transaction) {
                    $originalTransactionId = $transaction["seller_transaction_id"];
                    $sellerProtectionEligible = $transaction["seller_protection_eligible"];
                    try {
                        paymentReversed($disputeId, $originalTransactionId, 0, "paypalcheckout");
                        return "Dispute Resolved: Payment Reversed";
                    } catch (\Exception $e) {
                        throw new \WHMCS\Exception("Payment Reversal Could Not Be Completed: " . $e->getMessage());
                    }
                }
            }
            throw new \WHMCS\Exception("Dispute Resolved: No action");
        } else {
            throw new \WHMCS\Exception("Dispute Resolved: Unrecognised Status");
        }
    }
}

?>