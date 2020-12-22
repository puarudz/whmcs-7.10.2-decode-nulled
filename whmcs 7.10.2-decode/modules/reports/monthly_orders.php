<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Sales by Product for " . $months[(int) $month] . " " . $year;
$reportdata["description"] = "This report gives a breakdown of the number of units sold of each product per month";

$reportdata["currencyselections"] = true;

$total = 0;
$prevgroup = '';

$datefilter = Carbon::create(
    $year,
    $month,
    1
);

$reportdata["tableheadings"] = ["Product Name", "Units Sold", "Value",];

$results = Capsule::table('tblproducts')
    ->select(
        'tblproducts.id',
        'tblproducts.name',
        'tblproductgroups.name as groupname'
    )
    ->join('tblproductgroups', 'tblproducts.gid', '=', 'tblproductgroups.id')
    ->orderBy('tblproductgroups.order', 'asc')
    ->orderBy('tblproducts.order', 'asc')
    ->orderBy('tblproducts.name', 'asc')
    ->get();

foreach ($results as $result) {
    $pid = $result->id;
    $group = $result->groupname;
    $prodname = $result->name;

    if ($group != $prevgroup) {
        $reportdata["tablevalues"][] = ["**<b>$group</b>",];
    }

    $data = Capsule::table('tblhosting')
        ->select(
            [
                Capsule::raw('COUNT(tblhosting.id) as total'),
                Capsule::raw('SUM(tblhosting.firstpaymentamount) as amount')
            ]
        )
        ->where('tblhosting.packageid', $pid)
        ->where('tblhosting.domainstatus', 'Active')
        ->whereBetween(
            'tblhosting.regdate',
            [
                $datefilter->startOfMonth()->toDateTimeString(),
                $datefilter->endOfMonth()->toDateTimeString()
            ]
        )
        ->where('tblclients.currency', $currencyid)
        ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
        ->first();
    $number = $data->total;
    $amount = $data->amount;

    $total += $amount;

    $amount = formatCurrency($amount);

    $reportdata["tablevalues"][] = [$prodname, $number, $amount,];

    $prevgroup = $group;
}

$reportdata["tablevalues"][] = ["**<b>Addons</b>",];

$results = Capsule::table(tbladdons)
    ->orderBy('name', 'asc')
    ->get();

foreach ($results as $result) {
    $pid = $result->id;
    $prodname = $result->name;

    $data = Capsule::table('tblhostingaddons')
        ->select(
            [
                Capsule::raw('COUNT(tblhostingaddons.id) as total'),
                Capsule::raw('SUM(tblhostingaddons.setupfee + tblhostingaddons.recurring) as amount')
            ]
        )
        ->where('tblhostingaddons.addonid', $pid)
        ->where('tblhostingaddons.status', 'Active')
        ->whereBetween(
            'tblhostingaddons.regdate',
            [
                $datefilter->startOfMonth()->toDateTimeString(),
                $datefilter->endOfMonth()->toDateTimeString()
            ]
        )
        ->where('tblclients.currency', $currencyid)
        ->join('tblhosting', 'tblhosting.id', '=', 'tblhostingaddons.hostingid')
        ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
        ->first();
    $number = $data->total;
    $amount = $data->amount;

    $total += $amount;

    $amount = formatCurrency($amount);

    $reportdata["tablevalues"][] = [$prodname, $number, $amount,];

    $prevgroup = $group;
}

$total = formatCurrency($total);

$reportdata["footertext"] = '<p align="center"><strong>Total: ' . $total . '</strong></p>';

$reportdata["monthspagination"] = true;
