<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Top 10 Clients by Income";
$reportdata["description"] = "This report shows the 10 clients with the highest net income according to the transactions entered in WHMCS.";

$reportdata["tableheadings"] = array("Client ID","Client Name","Total Amount In","Total Fees","Total Amount Out","Balance");

$results = Capsule::table('tblaccounts')
    ->select(
        'tblclients.id',
        'tblclients.firstname',
        'tblclients.lastname',
        Capsule::raw('SUM(tblaccounts.amountin/tblaccounts.rate) AS amountIn'),
        Capsule::raw('SUM(tblaccounts.fees/tblaccounts.rate) AS fees'),
        Capsule::raw('SUM(tblaccounts.amountout/tblaccounts.rate) AS amountOut'),
        Capsule::raw('SUM((tblaccounts.amountin/tblaccounts.rate)-(tblaccounts.fees/tblaccounts.rate)-(tblaccounts.amountout/tblaccounts.rate)) AS balance'),
        'tblaccounts.rate'
    )
    ->join('tblclients', 'tblclients.id', '=', 'tblaccounts.userid')
    ->groupBy('userid')
    ->orderBy('balance', 'desc')
    ->take(10)
    ->get();

foreach ($results as $result) {
    $userid = $result->id;

    $currency = getCurrency();
    $rate = ($result->rate == "1.00000") ? '' : '*';

    $clientlink = '<a href="clientssummary.php?userid=' . $result->id . '">';

    $reportdata["tablevalues"][] = [
        $clientlink . $result->id . '</a>',
        $clientlink . $result->firstname . ' ' . $result->lastname . '</a>',
        formatCurrency($result->amountIn) . " $rate",
        formatCurrency($result->fees) . " $rate",
        formatCurrency($result->amountOut) . " $rate",
        formatCurrency($result->balance) . " $rate",
    ];

    $chartdata['rows'][] = [
        'c' => [
            [
                'v' => $result->firstname . ' ' . $result->lastname,
            ],
            [
                'v' => round($result->balance, 2),
                'f' => formatCurrency($result->balance),
            ]
        ]
    ];
}

$reportdata["footertext"] = "<p>* denotes converted to default currency</p>";

$chartdata['cols'][] = array('label'=>'Client','type'=>'string');
$chartdata['cols'][] = array('label'=>'Balance','type'=>'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Pie', $chartdata, $args, '300px');
