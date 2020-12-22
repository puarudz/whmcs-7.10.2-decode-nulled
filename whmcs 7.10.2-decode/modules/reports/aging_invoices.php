<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Aging Invoices";
$reportdata["description"] = "A summary of outstanding invoices broken down "
    . "into the period of which they are overdue";

$reportdata["tableheadings"][] = "Period";

foreach ($currencies as $currencyid => $currencyname) {
    $reportdata["tableheadings"][] = "{$currencyname} Amount";
}

$totals = [];
for ( $day = 0; $day < 120; $day += 30) {
    $startdate = date(
        "Y-m-d",
        mktime(0, 0, 0, date("m"), (date("d") - $day), date("Y"))
    );
    $enddate = date(
        "Y-m-d",
        mktime(0, 0, 0, date("m"), (date("d") - ($day + 30)), date("Y"))
    );
    $rowdata = [];
    $rowdata[] = "{$day} - " . ($day + 30);

    $currencytotals = [];
    $subQuery = Capsule::table('tblaccounts')
        ->select(Capsule::raw('sum(amountin - amountout)'))
        ->join('tblinvoices', 'tblinvoices.id', '=', 'tblaccounts.invoiceid')
        ->join('tblclients as t2', 't2.id', '=', 'tblinvoices.userid')
        ->where('tblinvoices.duedate', '<=', $startdate)
        ->where('tblinvoices.duedate', '>=', $enddate)
        ->where('tblinvoices.status', '=', 'Unpaid')
        ->where('t2.currency', '=', 'tblclients.currency');
    $results = Capsule::table('tblinvoices')
        ->select(
            'tblclients.currency',
            Capsule::raw('sum(tblinvoices.total) as `sum`'),
            Capsule::raw('(' . $subQuery->toSQL() . ') as `sum2`')
        )
        ->mergeBindings($subQuery)
        ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
        ->where('tblinvoices.duedate', '<=', $startdate)
        ->where('tblinvoices.duedate', '>=', $enddate)
        ->where('tblinvoices.status', '=', 'Unpaid')
        ->groupBy('tblclients.currency')
        ->get();
    foreach ($results as $result) {
        $currencytotals[$result->currency] = ($result->sum - $result->sum2);
    }

    foreach ($currencies as $currencyid => $currencyname) {
        $currencyamount = $currencytotals[$currencyid];
        if (!$currencyamount) {
            $currencyamount = 0;
        }
        $totals[$currencyid] += $currencyamount;
        $currency = getCurrency('', $currencyid);
        $rowdata[] = formatCurrency($currencyamount);
        if ($currencyid == $defaultcurrencyid) {
            $chartdata['rows'][] = [
                'c' => [
                    ['v' => "{$day} - " . ($day + 30)],
                    [
                        'v' => $currencyamount,
                        'f' => formatCurrency($currencyamount),
                    ],
                ]
            ];
        }
    }
    $reportdata["tablevalues"][] = $rowdata;
}

$startdate = date(
    "Y-m-d",
    mktime(0, 0, 0, date("m"), (date("d") - 120), date("Y"))
);
$rowdata = [];
$rowdata[] = "120 +";

$currencytotals = [];
$results = Capsule::table('tblinvoices')
    ->select(
        'tblclients.currency',
        Capsule::raw('sum(tblinvoices.total) as `sum`')
    )
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->where('tblinvoices.duedate', '<=', $startdate)
    ->where('tblinvoices.status', '=', 'Unpaid')
    ->groupBy('tblclients.currency')
    ->get();
foreach ($results as $result) {
    $currencytotals[$result->currency] = $result->sum;
}

foreach ($currencies as $currencyid => $currencyname) {
    $currencyamount = $currencytotals[$currencyid];
    if (!$currencyamount) {
        $currencyamount=0;
    }
    $totals[$currencyid] += $currencyamount;
    $currency = getCurrency('', $currencyid);
    $rowdata[] = formatCurrency($currencyamount);
    if ($currencyid == $defaultcurrencyid) {
        $chartdata['rows'][] = [
            'c' => [
                ['v' => "{$day} + "],
                [
                    'v' => $currencyamount,
                    'f' => formatCurrency($currencyamount),
                ],
            ]
        ];
    }

}
$reportdata["tablevalues"][] = $rowdata;

$rowdata = [];
$rowdata[] = "<b>Total</b>";
foreach ($currencies as $currencyid => $currencyname) {
    $currencytotal = $totals[$currencyid];
    if (!$currencytotal) {
        $currencytotal=0;
    }
    $currency = getCurrency('', $currencyid);
    $rowdata[] = "<b>" . formatCurrency($currencytotal) . "</b>";
}
$reportdata["tablevalues"][] = $rowdata;

$chartdata['cols'][] = ['label'=>'Days Range', 'type'=>'string'];
$chartdata['cols'][] = ['label'=>'Value', 'type'=>'number'];

$args = [];
$args['legendpos'] = 'right';

$reportdata["footertext"] = $chart->drawChart('Pie', $chartdata, $args, '300px');
