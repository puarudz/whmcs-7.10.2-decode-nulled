<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/** @type string $currentmonth */
/** @type int $currentyear */

$dateRange = Carbon::create($year, $month, 1);

$reportdata['title'] = "Support Ticket Replies for " . $currentmonth . " " . $currentyear;
$reportdata['description'] = "This report shows a breakdown of support tickets dealt with per admin for a given month";
$reportdata['monthspagination'] = true;

$reportdata['tableheadings'][] = "Admin";
for ($day = 1; $day <= $dateRange->endOfMonth()->day; $day++) {
    $reportdata['tableheadings'][] = $day;
}

$reportvalues = array();

$result = Capsule::table('tblticketreplies')
    ->where('admin', '!=', '')
    ->whereBetween(
        'date',
        [
            $dateRange->firstOfMonth()->toDateTimeString(),
            $dateRange->endOfMonth()->toDateTimeString(),
        ]
    )
    ->orderBy('admin')
    ->orderBy('date')
    ->groupBy(
        [
            'admin',
            Capsule::raw('date_format(date, \'%e\')'),
        ]
    )
    ->get(
        [
            'admin',
            Capsule::raw('date_format(date, \'%e\') as day_of_month'),
            Capsule::raw('COUNT(tid) as total_replies'),
            Capsule::raw('COUNT(DISTINCT tid) as total_tickets'),
        ]
    );

foreach ($result as $data) {
    $adminname = $data->admin;
    $day = $data->day_of_month;
    $reportvalues[$adminname][$day] = array(
        "totalreplies" => $data->total_replies,
        "totaltickets" => $data->total_tickets,
    );
}

$rc = 0;
foreach ($reportvalues as $adminname => $values) {
    $reportdata['tablevalues'][$rc][] = "**$adminname";

    $rc++;

    $reportdata['tablevalues'][$rc][] = "Tickets";
    $reportdata['tablevalues'][$rc+1][] = "Replies";

    for ($day = 1; $day <= $dateRange->endOfMonth()->day; $day++) {
        $reportdata['tablevalues'][$rc][] = isset($reportvalues[$adminname][$day]['totaltickets'])
            ? $reportvalues[$adminname][$day]['totaltickets']
            : '';
        $reportdata['tablevalues'][$rc+1][] = isset($reportvalues[$adminname][$day]['totalreplies'])
            ? $reportvalues[$adminname][$day]['totalreplies']
            : '';
    }
    $rc += 2;
}
