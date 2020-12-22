<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/** @var string $requeststr */

$reportdata["title"] = "New Customers";
$reportdata["description"] = "This report shows the total number of new customers, orders and complete orders and compares each of these to the previous year on the graph.";

$reportdata["tableheadings"] = array("Month","New Signups","Orders Placed","Orders Completed");

$show = App::getFromRequest('show');
if (!$show) {
    $show = 'signups';
}

for ($rawmonth = 1; $rawmonth <= 12; $rawmonth++) {
    $dateRange = Carbon::create($year, $rawmonth, 1);
    $dateRangeLastYear = Carbon::create(($year - 1), $rawmonth, 1);

    $firstOfMonth = $dateRange->firstOfMonth()->toDateTimeString();
    $firstOfMonth2 = $dateRangeLastYear->firstOfMonth()->toDateTimeString();
    $lastOfMonth = $dateRange->endOfMonth()->toDateTimeString();
    $lastOfMonth2 = $dateRangeLastYear->endOfMonth()->toDateTimeString();

    $newsignups = Capsule::table('tblclients')
        ->whereBetween(
            'datecreated',
            [
                $firstOfMonth,
                $lastOfMonth
            ]
        )->count();

    $totalorders = Capsule::table('tblorders')
        ->whereBetween(
            'date',
            [
                $firstOfMonth,
                $lastOfMonth
            ]
        )->count();

    $completedorders = Capsule::table('tblorders')
        ->where('status', 'Active')
        ->whereBetween(
            'date',
            [
                $firstOfMonth,
                $lastOfMonth
            ]
        )->count();

    $newsignups2 = Capsule::table('tblclients')
        ->whereBetween(
            'datecreated',
            [
                $firstOfMonth2,
                $lastOfMonth2
            ]
        )->count();

    $totalorders2 = Capsule::table('tblorders')
        ->whereBetween(
            'date',
            [
                $firstOfMonth2,
                $lastOfMonth2
            ]
        )->count();

    $completedorders2 = Capsule::table('tblorders')
        ->where('status', 'Active')
        ->whereBetween(
            'date',
            [
                $firstOfMonth2,
                $lastOfMonth2
            ]
        )->count();

    $reportdata["tablevalues"][] = array(
        $months[$rawmonth] . ' ' . $year,
        $newsignups,
        $totalorders,
        $completedorders
    );

    switch ($show) {
        case 'orders':
            $chartdata['rows'][] = array(
                'c' => array(
                    array('v' => $months[$rawmonth]),
                    array('v' => (int) $totalorders),
                    array('v' => (int) $totalorders2),
                ),
            );
            break;
        case 'orderscompleted':
            $chartdata['rows'][] = array(
                'c' => array(
                    array('v' => $months[$rawmonth]),
                    array('v' => (int) $completedorders),
                    array('v' => (int) $completedorders2),
                ),
            );
            break;
        case 'show':
        default:
            $chartdata['rows'][] = array(
                'c'=>array(
                    array('v' => $months[$rawmonth]),
                    array('v' => (int) $newsignups),
                    array('v' => (int) $newsignups2),
                ),
            );
    }
}

$chartdata['cols'][] = array('label' => 'Month','type' => 'string');
$chartdata['cols'][] = array('label' => $year,'type' => 'number');
$chartdata['cols'][] = array('label' => ($year - 1),'type' => 'number');

$args = array();
if (!$show || $show=="signups") {
    $args['title'] = 'New Signups';
    $args['colors'] = '#3366CC,#888888';
}
if ($show=="orders") {
    $args['title'] = 'Orders Placed';
    $args['colors'] = '#DC3912,#888888';
}
if ($show=="orderscompleted") {
    $args['title'] = 'Orders Completed';
    $args['colors'] = '#FF9900,#888888';
}
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Area', $chartdata, $args, '400px').
    '<p align="center">'.
        '<a href="reports.php' . $requeststr . '&show=signups">New Signups</a>'
        . ' | <a href="reports.php' . $requeststr . '&show=orders">Orders Placed</a>'
        . ' | <a href="reports.php' . $requeststr . '&show=orderscompleted">Orders Completed</a>'
    . '</p>';

$reportdata["yearspagination"] = true;
