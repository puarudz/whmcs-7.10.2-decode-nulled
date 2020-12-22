<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$today = Carbon::today();

$reportdata["title"] = "Affiliates Overview";
$reportdata["description"] = "An overview of affiliates for the current year";

$reportdata["tableheadings"] = [
    'Affiliate ID',
    'Affiliate Name',
    'Visitors',
    'Pending Commissions',
    'Available to Withdraw',
    'Withdrawn Amount',
    'YTD Total Commissions Paid',
];

$results = Capsule::table('tblaffiliates')
    ->select(
        'tblaffiliates.id',
        'tblaffiliates.clientid',
        'tblaffiliates.visitors',
        'tblaffiliates.balance',
        'tblaffiliates.withdrawn',
        'tblclients.firstname',
        'tblclients.lastname',
        'tblclients.companyname'
    )
    ->join('tblclients', 'tblclients.id', '=', 'tblaffiliates.clientid')
    ->orderBy('visitors', 'desc')
    ->get();
foreach ($results as $result) {
    $affid = $result->id;
    $clientid = $result->clientid;
    $visitors = $result->visitors;
    $balance = $result->balance;
    $withdrawn = $result->withdrawn;
    $firstname = $result->firstname;
    $lastname = $result->lastname;
    $companyname = $result->companyname;

    $name = $firstname . ' ' . $lastname;
    if ($companyname) {
        $name .= ' (' . $companyname . ')';
    }

    $pendingcommissionsamount = Capsule::table('tblaffiliatespending')
        ->join('tblaffiliatesaccounts', 'tblaffiliatesaccounts.id', '=', 'tblaffiliatespending.affaccid')
        ->join('tblhosting', 'tblhosting.id', '=', 'tblaffiliatesaccounts.relid')
        ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
        ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
        ->where('affiliateid', '=', $affid)
        ->orderBy('clearingdate', 'desc')
        ->sum('tblaffiliatespending.amount');

    $ytdtotal = Capsule::table('tblaffiliateshistory')
        ->where('affiliateid', $affid)
        ->whereBetween(
            'date',
            [
                $today->startOfYear()->toDateTimeString(),
                $today->endOfYear()->toDateTimeString(),
            ]
        )
        ->sum('amount');

    $currency = getCurrency($clientid);
    $pendingcommissionsamount = formatCurrency($pendingcommissionsamount);
    $ytdtotal = formatCurrency($ytdtotal);

    $reportdata["tablevalues"][] = [
        '<a href="affiliates.php?action=edit&id=' . $affid . '">' . $affid . '</a>',
        $name,
        $visitors,
        $pendingcommissionsamount,
        $balance,
        $withdrawn,
        $ytdtotal,
    ];
}
