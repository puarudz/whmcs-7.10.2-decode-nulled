<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Server Revenue Forecasts";
$reportdata["description"] = "This report shows income broken down by billing cycle for each of your servers."
    . " It then uses the monthly cost entered for each server to estimate the annual gross profit for"
    . " each server.";

$reportdata["tableheadings"] = [
    "Server Income",
    "Monthly",
    "Quarterly",
    "Semi-Annual",
    "Annual",
    "Biennial",
    "Triennial",
    "Monthly Costs",
    "Annual Gross Profit"
];

$currency = getCurrency('','1');

$results = Capsule::table('tblservers')
    ->where('disabled', '=', '0')
    ->orderBy('name', 'asc')
    ->get();
foreach ($results as $result) {
    $id = $result->id;
    $name = $result->name;
    $monthlycost = $result->monthlycost;
    $monthly = $quarterly = $semiannually = $annually = $biennially = $triennially = 0;

    $services = Capsule::table('tblhosting')
        ->select(
            'tblhosting.billingcycle',
            Capsule::raw('tblhosting.amount/tblcurrencies.rate AS reportamt')
        )
        ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
        ->join('tblcurrencies', 'tblcurrencies.id', '=', 'tblclients.currency')
        ->where('server', '=', (int) $id)
        ->whereIn('domainstatus', ['Active', 'Suspended'])
        ->whereNotIn('billingcycle', ['Free Account', 'One Time'])
        ->get();
    foreach ($services as $service) {
        $amount = $service->reportamt;
        $billingcycle = $service->billingcycle;

        if ($billingcycle == "Monthly") {
            $monthly += $amount;
        } elseif ($billingcycle == "Quarterly") {
            $quarterly += $amount;
        } elseif ($billingcycle == "Semi-Annually") {
            $semiannually += $amount;
        } elseif ($billingcycle == "Annually") {
            $annually += $amount;
        } elseif ($billingcycle == "Biennially") {
            $biennially += $amount;
        } elseif ($billingcycle == "Triennially") {
            $triennially += $amount;
        }
    }
    
    $monthly = number_format($monthly, 2, ".", "");
    $quarterly = number_format($quarterly, 2, ".", "");
    $semiannually = number_format($semiannually, 2, ".", "");
    $annually = number_format($annually, 2, ".", "");
    $biennially = number_format($biennially, 2, ".", "");
    $triennially = number_format($triennially, 2, ".", "");
    $totalserverincome = (
        ($monthly * 12)
        + ($quarterly * 4)
        + ($semiannually * 2)
        + $annually
        + ($biennially / 2)
        + ($triennially / 3)
    );
    
    $totalserverexpenditure = ($monthlycost * 12);
    $servertotal = number_format(($totalserverincome - $totalserverexpenditure), 2, ".", "");
    $totalincome += $totalserverincome;
    $totalexpenditure += $totalserverexpenditure;
    $totalgrossprofit += $servertotal;
    $reportdata["tablevalues"][] = [
        "$name",
        formatCurrency($monthly),
        formatCurrency($quarterly),
        formatCurrency($semiannually),
        formatCurrency($annually),
        formatCurrency($biennially),
        formatCurrency($triennially),
        formatCurrency($monthlycost),
        formatCurrency($servertotal)
    ];
}

$totalincome = formatCurrency($totalincome);
$totalexpenditure = formatCurrency($totalexpenditure);
$totalgrossprofit = formatCurrency($totalgrossprofit);

$data["footertext"] = "<strong>Total Income:</strong> {$totalincome}<br />"
    . "<strong>Total Expenses:</strong> {$totalexpenditure}<br />"
    . "<strong>Gross Profit:</strong> {$totalgrossprofit}";
