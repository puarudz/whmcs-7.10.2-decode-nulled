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

/** @var Carbon $today */
$startOfMonth = $dateFilter->startOfMonth()->toDateTimeString();
$endOfMonth = $dateFilter->endOfMonth()->toDateTimeString();

$reportdata["title"] = "Daily Performance for " . $months[(int) $month] . " " . $year;
$reportdata["description"] = "This report shows a daily activity summary for a given month.";

$reportdata["monthspagination"] = true;

$reportdata["tableheadings"] = array(
    "Date",
    "Completed Orders",
    "New Invoices",
    "Paid Invoices",
    "Opened Tickets",
    "Ticket Replies",
    "Cancellation Requests",
);

$reportvalues = array();

$dateFormat = Capsule::raw('date_format(`date`, "%e")');
$reportvalues['orders_active'] = Capsule::table('tblorders')
    ->where('status', 'Active')
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->groupBy($dateFormat)
    ->orderBy('date')
    ->pluck(Capsule::raw('count(id) as total'), Capsule::raw('date_format(`date`, "%e") as day'));

$reportvalues['invoices_new'] = Capsule::table('tblinvoices')
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->groupBy($dateFormat)
    ->orderBy('date')
    ->pluck(Capsule::raw('count(id) as total'), Capsule::raw('date_format(`date`, "%e") as day'));

$reportvalues['invoices_paid'] = Capsule::table('tblinvoices')
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->groupBy(Capsule::raw('date_format(`datepaid`, "%e")'))
    ->orderBy('date')
    ->pluck(Capsule::raw('count(id) as total'), Capsule::raw('date_format(`datepaid`, "%e") as day'));

$reportvalues['tickets_new'] = Capsule::table('tbltickets')
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->groupBy($dateFormat)
    ->orderBy('date')
    ->pluck(Capsule::raw('count(id) as total'), Capsule::raw('date_format(`date`, "%e") as day'));

$reportvalues['tickets_staff_replies'] = Capsule::table('tblticketreplies')
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->where('admin', '!=', '')
    ->groupBy($dateFormat)
    ->orderBy('date')
    ->pluck(Capsule::raw('count(id) as total'), Capsule::raw('date_format(`date`, "%e") as day'));

$reportvalues['cancellations_new'] = Capsule::table('tblcancelrequests')
    ->whereBetween(
        'date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    )
    ->groupBy($dateFormat)
    ->orderBy('date')
    ->pluck(Capsule::raw('count(id) as total'), Capsule::raw('date_format(`date`, "%e") as day'));

for ($day = 1; $day <= $dateFilter->endOfMonth()->day; $day++) {
    $date = Carbon::create($year, $month, $day);
    $daytext = $date->format('l');
    $date = $date->toDateString();

    $neworders = isset($reportvalues['orders_active'][$day]) ? $reportvalues['orders_active'][$day] : '0';
    $newinvoices = isset($reportvalues['invoices_new'][$day]) ? $reportvalues['invoices_new'][$day] : '0';
    $paidinvoices = isset($reportvalues['invoices_paid'][$day]) ? $reportvalues['invoices_paid'][$day] : '0';
    $newtickets = isset($reportvalues['tickets_new'][$day]) ? $reportvalues['tickets_new'][$day] : '0';
    $ticketreplies = isset($reportvalues['tickets_staff_replies'][$day]) ? $reportvalues['tickets_staff_replies'][$day] : '0';
    $cancellations = isset($reportvalues['cancellations_new'][$day]) ? $reportvalues['cancellations_new'][$day] : '0';

    $reportdata["tablevalues"][] = array(
        $daytext.' '.fromMySQLDate($date),
        $neworders,
        $newinvoices,
        $paidinvoices,
        $newtickets,
        $ticketreplies,
        $cancellations,
    );

    $chartdata['rows'][] = array(
        'c'=>array(
            array('v' => fromMySQLDate($date)),
            array('v' => (int)$neworders),
            array('v' => (int)$newinvoices),
            array('v' => (int)$paidinvoices),
            array('v' => (int)$newtickets),
            array('v' => (int)$ticketreplies),
            array('v' => (int)$cancellations)
        )
    );

}

$chartdata['cols'][] = array('label'=>'Day','type'=>'string');
$chartdata['cols'][] = array('label'=>'Completed Orders','type'=>'number');
$chartdata['cols'][] = array('label'=>'New Invoices','type'=>'number');
$chartdata['cols'][] = array('label'=>'Paid Invoices','type'=>'number');
$chartdata['cols'][] = array('label'=>'Opened Tickets','type'=>'number');
$chartdata['cols'][] = array('label'=>'Ticket Replies','type'=>'number');
$chartdata['cols'][] = array('label'=>'Cancellation Requests','type'=>'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Area',$chartdata,$args,'400px');
