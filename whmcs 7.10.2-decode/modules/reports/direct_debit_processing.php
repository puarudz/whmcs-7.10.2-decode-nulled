<?php

use WHMCS\Billing\Invoice;
use WHMCS\Database\Capsule;
use WHMCS\Payment\PayMethod\Adapter\BankAccount;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!function_exists('getClientDefaultBankDetails')) {
    require ROOTDIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'clientfunctions.php';
}

$reportdata["title"] = "Direct Debit Processing";
$reportdata["description"] = "This report displays all Unpaid invoices assigned to the Direct Debit payment method and the associated bank account details stored for their owners ready for processing";

$reportdata["tableheadings"] = array("Invoice ID","Client Name","Invoice Date","Due Date","Subtotal","Tax","Credit","Total","Bank Name","Bank Account Type","Bank Code","Bank Account Number");

$defaultBankDetailsPerUser = [];

$results = Capsule::table('tblinvoices')
    ->select('tblinvoices.*', 'tblclients.firstname', 'tblclients.lastname')
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->where('tblinvoices.paymentmethod', '=', 'directdebit')
    ->where('tblinvoices.status', '=', 'Unpaid')
    ->orderBy('duedate', 'asc')
    ->get();
foreach ($results as $result) {
    $id = $result->id;
    $userid = $result->userid;
    $client = $result->firstname . " " . $result->lastname;
    $date = $result->date;
    $duedate = $result->duedate;
    $subtotal = $result->subtotal;
    $credit = $result->credit;
    $tax = ($result->tax + $result->tax2);
    $total = $result->total;

    $invoice = Invoice::find($id);

    if ($invoice && $invoice->payMethod && $invoice->payMethod->payment->isBankAccount()) {
        /** @var BankAccount $payment */
        $payment = $invoice->payMethod->payment;

        $bankDetails["bankname"] = $payment->getBankName();
        $bankDetails["banktype"] = $payment->getAccountType();
        $bankDetails["bankcode"] = $payment->getRoutingNumber();
        $bankDetails["bankacct"] = $payment->getAccountNumber();
    } else {
        if (!isset($defaultBankDetailsPerUser[$userid])) {
            $defaultBankDetailsPerUser[$userid] = getClientDefaultBankDetails($userid);
        }

        $bankDetails = $defaultBankDetailsPerUser[$userid];
    }

    $bankname = $bankDetails["bankname"];
    $banktype = $bankDetails["banktype"];
    $bankcode = $bankDetails["bankcode"];
    $bankacct = $bankDetails["bankacct"];

    $currency = getCurrency($userid);
    $date = fromMySQLDate($date);
    $duedate = fromMySQLDate($duedate);
    $subtotal = formatCurrency($subtotal);
    $credit = formatCurrency($credit);
    $tax = formatCurrency($tax);
    $total = formatCurrency($total);

    $reportdata["tablevalues"][] = [
        '<a href="invoices.php?action=edit&id=' . $id . '">' . $id . '</a>',
        $client,
        $date,
        $duedate,
        $subtotal,
        $tax,
        $credit,
        $total,
        $bankname,
        $banktype,
        $bankcode,
        $bankacct,
    ];
}

$reportdata["footertext"] = "";
