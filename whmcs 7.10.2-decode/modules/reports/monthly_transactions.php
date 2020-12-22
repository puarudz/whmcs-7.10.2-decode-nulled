<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$dateFilter = Carbon::create(
    $year,
    $month,
    1
);

$startOfMonth = $dateFilter->startOfMonth()->toDateTimeString();
$endOfMonth = $dateFilter->endOfMonth()->toDateTimeString();

$reportdata["title"] = "Monthly Transactions Report for " . $months[(int) $month] . " " . $year;
$reportdata["description"] = "This report provides a summary of daily payments activity for a given month. The Amount Out figure includes both expenditure transactions and refunds.";

$reportdata["currencyselections"] = true;
$reportdata["monthspagination"] = true;

$reportdata["tableheadings"] = array(
    "Date",
    "Amount In",
    "Fees",
    "Amount Out",
    "Balance"
);

$reportvalues = array();
$dateFormat = Capsule::raw('date_format(date, "%e")');

$result = Capsule::table('tblaccounts')
    ->join('tblclients', 'tblclients.id', '=', 'tblaccounts.userid')
    ->where('tblclients.currency', $currencyid)
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->orderBy('date')
    ->groupBy($dateFormat)
    ->get(
        [
            Capsule::raw('date_format(date, "%e") as day_of_month'),
            Capsule::raw('SUM(amountin) as in_amount'),
            Capsule::raw('SUM(fees) as fee_amount'),
            Capsule::raw('SUM(amountout) as out_amount'),
        ]
    );

foreach ($result as $data) {
    $reportvalues[$data->day_of_month] = array(
        'amountin' => $data->in_amount,
        'fees' => $data->fee_amount,
        'amountout' => $data->out_amount,
    );
}

$result = Capsule::table('tblaccounts')
    ->where('userid', 0)
    ->where('currency', $currencyid)
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->orderBy('date')
    ->groupBy($dateFormat)
    ->get(
        [
            Capsule::raw('date_format(date, "%e") as day_of_month'),
            Capsule::raw('SUM(amountin) as in_amount'),
            Capsule::raw('SUM(fees) as fee_amount'),
            Capsule::raw('SUM(amountout) as out_amount'),
        ]
    );

foreach ($result as $data) {
    $reportvalues[$data->day_of_month] = array(
        'amountin' => $data->in_amount,
        'fees' => $data->fee_amount,
        'amountout' => $data->out_amount,
    );
}

for ($dayOfTheMonth = 1; $dayOfTheMonth <= $dateFilter->lastOfMonth()->day; $dayOfTheMonth++) {
    $amountin = isset($reportvalues[$dayOfTheMonth]['amountin']) ? $reportvalues[$dayOfTheMonth]['amountin'] : '0';
    $fees = isset($reportvalues[$dayOfTheMonth]['fees']) ? $reportvalues[$dayOfTheMonth]['fees'] : '0';
    $amountout = isset($reportvalues[$dayOfTheMonth]['amountout']) ? $reportvalues[$dayOfTheMonth]['amountout'] : '0';
    $dailybalance = $amountin-$fees-$amountout;
    $overallbalance += $dailybalance;
    $chartdata['rows'][] = array('c'=>array(array('v'=>$dayOfTheMonth),array('v'=>$amountin,'f'=>formatCurrency($amountin)),array('v'=>$fees,'f'=>formatCurrency($fees)),array('v'=>$amountout,'f'=>formatCurrency($amountout))));
    $amountin = formatCurrency($amountin);
    $fees = formatCurrency($fees);
    $amountout = formatCurrency($amountout);
    $dailybalance = formatCurrency($dailybalance);
    $dayOfTheMonth = str_pad($dayOfTheMonth, 2, "0", STR_PAD_LEFT);
    $reportdata["tablevalues"][] = array(
        fromMySQLDate("$year-$month-$dayOfTheMonth"),
        $amountin,
        $fees,
        $amountout,
        $dailybalance,
    );
}

$reportdata["footertext"] = '<p align="center"><strong>Balance: ' . formatCurrency($overallbalance) . '</strong></p>';

$chartdata['cols'][] = array('label'=>'Days Range','type'=>'string');
$chartdata['cols'][] = array('label'=>'Amount In','type'=>'number');
$chartdata['cols'][] = array('label'=>'Fees','type'=>'number');
$chartdata['cols'][] = array('label'=>'Amount Out','type'=>'number');

$args['colors'] = '#80D044,#F9D88C,#CC0000';

$reportdata["headertext"] = $chart->drawChart('Area', $chartdata, $args, '450px');
