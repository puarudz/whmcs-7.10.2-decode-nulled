<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Ticket Tags Overview";
$reportdata["description"] = "This report provides an overview of ticket tags assigned to tickets for a given date range";

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subMonth()->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}
$dateRange = Carbon::parseDateRangeValue($range);
$startdate = $dateRange['from'];
$enddate = $dateRange['to'];

$reportdata['headertext'] = '';
if (!$print) {
    $reportdata['headertext'] = <<<HTML
<form method="post" action="reports.php?report={$report}&currencyid={$currencyid}&calculate=true">
    <div class="report-filters-wrapper">
        <div class="inner-container">
            <h3>Filters</h3>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputFilterDate">{$dateRangeText}</label>
                        <div class="form-group date-picker-prepend-icon">
                            <label for="inputFilterDate" class="field-icon">
                                <i class="fal fa-calendar-alt"></i>
                            </label>
                            <input id="inputFilterDate"
                                   type="text"
                                   name="range"
                                   value="{$range}"
                                   class="form-control date-picker-search"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                {$aInt->lang('reports', 'generateReport')}
            </button>
        </div>
    </div>
</form>
HTML;
}

$reportdata["tableheadings"][] = "Tag";
$reportdata["tableheadings"][] = "Count";

$results = Capsule::table('tbltickettags')
    ->select(Capsule::raw('tbltickettags.tag, count(*) as `count`'))
    ->join('tbltickets', 'tbltickets.id', '=', 'tbltickettags.ticketid')
    ->whereBetween(
        'tbltickets.date',
        [
            $startdate->toDateTimeString(),
            $enddate->toDateTimeString(),
        ]
    )
    ->groupBy('tbltickettags.tag')
    ->orderBy('count', 'desc')
    ->get();

foreach ($results as $result) {
    $tag = $result->tag;
    $count = $result->count;

    $reportdata["tablevalues"][] = [$tag, $count];
    $chartdata['rows'][] = [
        'c' => [
            ['v' => $tag],
            ['v' => (int) $count, 'f' => $count],
        ],
    ];
}

$chartdata['cols'][] = array('label'=>'Tag','type'=>'string');
$chartdata['cols'][] = array('label'=>'Count','type'=>'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] .= $chart->drawChart('Pie',$chartdata,$args,'300px');
