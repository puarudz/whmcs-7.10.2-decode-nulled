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
$GATEWAY = getGatewayVariables("paypal");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
$postipn = "cmd=_notify-validate";
$orgipn = "";
foreach ($_POST as $key => $value) {
    $orgipn .= (string) $key . " => " . $value . "\n";
    $postipn .= "&" . $key . "=" . urlencode(WHMCS\Input\Sanitize::decode($value));
}
if ($GATEWAY["sandbox"]) {
    $url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
} else {
    $url = "https://www.paypal.com/cgi-bin/webscr";
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postipn);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 100);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, "WHMCS V" . $whmcs->getVersion()->getCasual());
$reply = curl_exec($ch);
curl_close($ch);
if (!strcmp($reply, "VERIFIED")) {
    $paypalemail = $_POST["receiver_email"];
    $payment_status = $_POST["payment_status"];
    $subscr_id = $_POST["subscr_id"];
    $old_subscr_id = $_POST["old_subscr_id"];
    $txn_type = $_POST["txn_type"];
    $txn_id = $_POST["txn_id"];
    $mc_gross = $_POST["mc_gross"];
    $mc_fee = $_POST["mc_fee"];
    $idnumber = $_POST["custom"];
    $paypalcurrency = $_REQUEST["mc_currency"];
    $upgradeInvoice = false;
    if (substr($idnumber, 0, 1) == "U") {
        $idnumber = (int) substr($idnumber, 1);
        $upgradeInvoice = true;
    }
    $paypalemails = explode(",", strtolower($GATEWAY["email"]));
    array_walk($paypalemails, "paypal_email_trim");
    if (!in_array(strtolower($paypalemail), $paypalemails)) {
        logTransaction($GATEWAY["paymentmethod"], $orgipn, "Invalid Receiver Email");
        exit;
    }
    if ($payment_status == "Pending") {
        logTransaction($GATEWAY["paymentmethod"], $orgipn, "Pending");
        exit;
    }
    if ($txn_id) {
        checkCbTransID($txn_id);
    }
    if (!is_numeric($idnumber)) {
        $idnumber = "";
    }
    if ($txn_type == "web_accept" && $_POST["invoice"] && $payment_status == "Completed") {
        WHMCS\Database\Capsule::table("tblaccounts")->where("transid", $txn_id)->update(array("fees" => $mc_fee));
    }
    if ($txn_type === "recurring_payment_suspended_due_to_max_failed_payment") {
        $subscr_id = $_POST["recurring_payment_id"];
        $invoice = paypal_findinvoicebysubscriptionid($subscr_id);
        $passedParams = array();
        if (isset($invoice["invoiceid"])) {
            $history = WHMCS\Billing\Payment\Transaction\History::create(array("invoice_id" => $invoice["invoiceid"], "gateway" => $GATEWAY["paymentmethod"]));
            $history->remoteStatus = $txn_type;
            $history->description = "Recurring payment failed and the related recurring payment profile has been suspended";
            $history->completed = false;
            $history->save();
            $passedParams["history_id"] = $history->id;
        }
        logTransaction($GATEWAY["paymentmethod"], $orgipn, "Recurring Payment Suspended", $passedParams);
        exit;
    }
    $data = WHMCS\Database\Capsule::table("tblcurrencies")->where("code", $paypalcurrency)->first();
    $paypalcurrencyid = $data->id;
    $currencyconvrate = $data->rate;
    if (!$paypalcurrencyid) {
        logTransaction($GATEWAY["paymentmethod"], $orgipn, "Unrecognised Currency");
        exit;
    }
    $invoiceid = 0;
    switch ($txn_type) {
        case "subscr_signup":
            logTransaction($GATEWAY["paymentmethod"], $orgipn, "Subscription Signup");
            exit;
        case "subscr_cancel":
            WHMCS\Database\Capsule::table("tblhosting")->where("subscriptionid", $subscr_id)->update(array("subscriptionid" => ""));
            logTransaction($GATEWAY["paymentmethod"], $orgipn, "Subscription Cancelled");
            exit;
        case "subscr_payment":
            if ($payment_status != "Completed") {
                logTransaction($GATEWAY["paymentmethod"], $orgipn, "Incomplete");
                exit;
            }
            if ($upgradeInvoice) {
                $data = WHMCS\Database\Capsule::table("tblinvoiceitems")->join("tblinvoices", "tblinvoices.id", "=", "tblinvoiceitems.invoiceid")->join("tblupgrades", "tblupgrades.id", "=", "tblinvoiceitems.relid")->where("tblupgrades.relid", $idnumber)->where("tblupgrades.paid", "N")->where("tblinvoiceitems.type", "Upgrade")->where("tblinvoices.status", "Unpaid")->orderBy("tblinvoices.id", "asc")->first(array("tblinvoices.id", "tblinvoices.userid"));
                $invoiceid = $data->id;
                $userid = $data->userid;
                if ($invoiceid) {
                    $orgipn .= "Invoice Found from Upgrade ID Match => " . $invoiceid . "\n";
                }
            }
            if (!$invoiceid) {
                $invoiceid = NULL;
                $userid = NULL;
                $data = paypal_findinvoicebycustomid($idnumber);
                if ($data["invoiceid"]) {
                    $invoiceid = $data["invoiceid"];
                    $userid = $data["userid"];
                    $orgipn .= "Invoice Found from Product ID Match => " . $invoiceid . "\n";
                } else {
                    $data = paypal_findinvoicebysubscriptionid($subscr_id);
                    if ($data["invoiceid"]) {
                        $invoiceid = $data["invoiceid"];
                        $userid = $data["userid"];
                        $orgipn .= "Invoice Found from Subscription ID Match => " . $invoiceid . "\n";
                    } else {
                        $data = paypal_findinvoicebysubscriptionid($old_subscr_id);
                        if ($data["invoiceid"]) {
                            $invoiceid = $data["invoiceid"];
                            $userid = $data["userid"];
                            $orgipn .= "Invoice Found from Old Subscription ID Match => " . $invoiceid . "\n";
                        }
                    }
                }
                if (!$invoiceid) {
                    $invoiceitemsInvoiceIds = array();
                    if ($idnumber) {
                        $invoiceitemsInvoiceIds = WHMCS\Database\Capsule::table("tblinvoiceitems")->where("relid", $idnumber)->where("type", "Hosting")->pluck("invoiceid");
                    }
                    if ($invoiceitemsInvoiceIds) {
                        $lastPaidInvoice = WHMCS\Database\Capsule::table("tblinvoices")->where("status", "Paid")->whereIn("id", $invoiceitemsInvoiceIds)->orderBy("id", "desc")->first(array("id", "userid"));
                        $invoiceid = $lastPaidInvoice->id;
                        $userid = $lastPaidInvoice->userid;
                    } else {
                        $invoiceid = NULL;
                        $userid = NULL;
                    }
                    if ($invoiceid) {
                        $orgipn .= "Paid Invoice Found from Product ID Match => " . $invoiceid . "\n";
                    }
                }
            }
            break;
        case "subscr_failed":
            $invoice = paypal_findinvoicebysubscriptionid($subscr_id);
            $passedParams = array();
            if (isset($invoice["invoiceid"])) {
                $history = WHMCS\Billing\Payment\Transaction\History::create(array("invoice_id" => $invoice["invoiceid"], "gateway" => $GATEWAY["paymentmethod"]));
                $history->remoteStatus = $txn_type;
                $history->description = "Subscription payment failed";
                $history->completed = false;
                $history->save();
                $passedParams["history_id"] = $history->id;
            }
            logTransaction($GATEWAY["paymentmethod"], $orgipn, "Subscription Failed", $passedParams);
            exit;
        case "web_accept":
            if ($payment_status != "Completed") {
                logTransaction($GATEWAY["paymentmethod"], $orgipn, "Incomplete");
                exit;
            }
            $invoice = WHMCS\Database\Capsule::table("tblinvoices")->find($idnumber, array("id", "userid"));
            if ($invoice) {
                $invoiceid = $invoice->id;
                $userid = $invoice->userid;
            }
            break;
    }
    if (!$txn_type && $payment_status == "Reversed") {
        $originalTransactionId = App::getFromRequest("parent_txn_id");
        try {
            paymentReversed($txn_id, $originalTransactionId, 0, "paypal");
            logTransaction("PayPal", $orgipn, "Payment Reversed");
        } catch (Exception $e) {
            logTransaction("PayPal", $orgipn, "Payment Reversal Could Not Be Completed: " . $e->getMessage());
        }
        exit;
    }
    $reasonCode = App::getFromRequest("reason_code");
    if (!$txn_type && $payment_status === "Refunded" && $reasonCode === "buyer_complaint") {
        if (!App::isInRequest("parent_txn_id")) {
            logTransaction("PayPal", $orgipn, "Not Supported");
            exit;
        }
        if (!function_exists("getCCVariables")) {
            require_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "ccfunctions.php";
        }
        $parentTransactionID = App::getFromRequest("parent_txn_id");
        $transaction = WHMCS\Billing\Payment\Transaction::where("transid", $parentTransactionID)->first();
        if (is_null($transaction)) {
            logActivity("PayPal", $orgipn, "Transaction Not Found");
            exit;
        }
        if ($mc_gross < 0) {
            $mc_gross *= -1;
        }
        if ($mc_fee < 0) {
            $mc_fee *= -1;
        }
        $currency = getCurrency($userid);
        if ($paypalcurrencyid != $currency["id"]) {
            $mc_gross = convertCurrency($mc_gross, $paypalcurrencyid, $currency["id"]);
            $mc_fee = convertCurrency($mc_fee, $paypalcurrencyid, $currency["id"]);
        }
        $invoice = $transaction->invoice;
        if ($invoice) {
            $reverse = false;
            if (valueIsZero($mc_gross - $invoice->total)) {
                $reverse = true;
            }
            $refund = refundInvoicePayment($transaction->id, $mc_gross, false, false, true, $txn_id, $reverse);
            logTransaction("PayPal", $orgipn, "Invoice Refunded - Buyer Complaint");
            exit;
        }
        logTransaction("PayPal", $orgipn, "Invoice Not Found");
        exit;
    }
    if ($invoiceid) {
        logTransaction($GATEWAY["paymentmethod"], $orgipn, "Successful");
        $currency = getCurrency($userid);
        if ($paypalcurrencyid != $currency["id"]) {
            $mcGrossInCallback = $mc_gross;
            $mc_gross = convertCurrency($mc_gross, $paypalcurrencyid, $currency["id"]);
            $mc_fee = convertCurrency($mc_fee, $paypalcurrencyid, $currency["id"]);
            $total = WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->value("total");
            $grossExpected = convertCurrency($total, $currency["id"], $paypalcurrencyid);
            if (abs($mcGrossInCallback - $grossExpected) < 1) {
                $mc_gross = $total;
            }
        }
        addInvoicePayment($invoiceid, $txn_id, $mc_gross, $mc_fee, "paypal");
        $relid = WHMCS\Database\Capsule::table("tblinvoiceitems")->where("invoiceid", $invoiceid)->where("type", "Hosting")->value("relid");
        if ($upgradeInvoice && !empty($upgradeID)) {
            $relid = WHMCS\Database\Capsule::table("tblupgrades")->where("id", $upgradeID)->value("relid");
        }
        if ($relid) {
            WHMCS\Database\Capsule::table("tblhosting")->where("id", $relid)->update(array("subscriptionid" => $subscr_id));
        }
        exit;
    }
    if ($txn_type == "subscr_payment") {
        if ($subscr_id) {
            $userid = WHMCS\Database\Capsule::table("tblhosting")->where("subscriptionid", $subscr_id)->value("userid");
        }
        if ($userid) {
            $orgipn .= "User ID Found from Subscription ID Match: User ID => " . $userid . "\n";
            $currency = getCurrency($userid);
            if ($paypalcurrencyid != $currency["id"]) {
                $mc_gross = convertCurrency($mc_gross, $paypalcurrencyid, $currency["id"]);
                $mc_fee = convertCurrency($mc_fee, $paypalcurrencyid, $currency["id"]);
            }
            WHMCS\Database\Capsule::table("tblaccounts")->insert(array("userid" => $userid, "currency" => $currency["id"], "gateway" => "paypal", "date" => WHMCS\Carbon::now()->toDateTimeString(), "description" => "PayPal Subscription Payment", "amountin" => $mc_gross, "fees" => $mc_fee, "rate" => $currencyconvrate, "transid" => $txn_id));
            WHMCS\Database\Capsule::table("tblcredit")->insert(array("clientid" => $userid, "date" => WHMCS\Carbon::now()->toDateTimeString(), "description" => "PayPal Subscription Transaction ID " . $txn_id, "amount" => $mc_gross));
            WHMCS\Database\Capsule::table("tblclients")->where("id", (int) $userid)->increment("credit", $mc_gross);
            logTransaction($GATEWAY["paymentmethod"], $orgipn, "Credit Added");
        } else {
            logTransaction($GATEWAY["paymentmethod"], $orgipn, "Invoice Not Found");
        }
    } else {
        logTransaction($GATEWAY["paymentmethod"], $orgipn, "Not Supported");
    }
} else {
    if (!strcmp($reply, "INVALID")) {
        logTransaction($GATEWAY["paymentmethod"], $orgipn, "IPN Handshake Invalid");
        header("HTTP/1.0 406 Not Acceptable");
        exit;
    }
    logTransaction($GATEWAY["paymentmethod"], $orgipn . "\n\nIPN Handshake Response => " . $reply, "IPN Handshake Error");
    header("HTTP/1.0 406 Not Acceptable");
    exit;
}
function paypal_findInvoiceBySubscriptionId($subscr_id = "")
{
    $data = array("invoiceid" => NULL, "userid" => NULL);
    if ($subscr_id) {
        $subscr_id = db_escape_string($subscr_id);
        $row = WHMCS\Database\Capsule::table("tblhosting")->join("tblinvoiceitems", "tblhosting.id", "=", "tblinvoiceitems.relid")->join("tblinvoices", "tblinvoices.id", "=", "tblinvoiceitems.invoiceid")->where("tblinvoices.status", "Unpaid")->where("tblhosting.subscriptionid", $subscr_id)->where("tblinvoiceitems.type", "Hosting")->orderBy("tblinvoiceitems.invoiceid", "asc")->first(array("tblinvoiceitems.invoiceid", "tblinvoices.userid"));
        $data["invoiceid"] = $row->invoiceid;
        $data["userid"] = $row->userid;
    }
    return $data;
}
function paypal_findInvoiceByCustomId($idnumber = "")
{
    $invoiceitemsInvoiceIds = array();
    $data = array("invoiceid" => NULL, "userid" => NULL);
    if ($idnumber) {
        $invoiceitemsInvoiceIds = WHMCS\Database\Capsule::table("tblinvoiceitems")->where("relid", $idnumber)->where("type", "Hosting")->pluck("invoiceid");
    }
    if ($invoiceitemsInvoiceIds) {
        $firstUnpaidInvoice = WHMCS\Database\Capsule::table("tblinvoices")->where("status", "Unpaid")->whereIn("id", $invoiceitemsInvoiceIds)->orderBy("id", "asc")->first(array("id", "userid"));
        if ($firstUnpaidInvoice) {
            $data["invoiceid"] = $firstUnpaidInvoice->id;
            $data["userid"] = $firstUnpaidInvoice->userid;
        }
    }
    return $data;
}
function paypal_email_trim(&$value)
{
    $value = trim($value);
}

?>