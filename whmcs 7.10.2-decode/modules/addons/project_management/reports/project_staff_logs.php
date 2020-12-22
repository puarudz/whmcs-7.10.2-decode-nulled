<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Project Management Staff Logs";
$reportdata["description"] = "This report shows the amount of time logged per member of staff, per day, over a customisable date range.";

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subDays(6)->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}

$reportdata['headertext'] = '';
if (!$print) {
    $reportdata['headertext'] = <<<HTML
<form method="post" action="{$requeststr}">
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

$dateRange = Carbon::parseDateRangeValue($range);
$diffInDays = $dateRange['from']->diffInDays($dateRange['to']);
$datefromsql = $dateRange['from']->toDateTimeString();
$datetosql = $dateRange['to']->toDateTimeString();

$reportdata["tableheadings"] = array("Staff Member");

$startday = substr($datefromsql, 8, 2);
$startmonth = substr($datefromsql, 5, 2);
$startyear = substr($datefromsql, 0, 4);

for ($i = 0; $i <= $diffInDays; $i++) {
    $date = date("Y-m-d",mktime(0,0,0,$startmonth,$startday+$i,$startyear));
    $reportdata["tableheadings"][] = $date;
    if (str_replace('-','',$date)==str_replace('-','',$datetosql)) break;
}

$reportdata["tableheadings"][] = "Totals";

$daytotals = array();
$r = 0;

$results = Capsule::table('tbladmins')
    ->orderBy('firstname', 'asc')
    ->get(['id', 'firstname', 'lastname']);
foreach ($results as $data) {
    $adminid = $data->id;
    $firstname = $data->firstname;
    $lastname = $data->lastname;

    $reportdata["tablevalues"][$r] = array($firstname.' '.$lastname);

    $totalduration = 0;

    for ($i = 0; $i <= $diffInDays; $i++) {
        $date = date("Y-m-d",mktime(0,0,0,$startmonth,$startday+$i,$startyear));
        $datestart = mktime(0,0,0,$startmonth,$startday+$i,$startyear);
        $dateend = mktime(0,0,0,$startmonth,$startday+$i+1,$startyear);

        $duration = 0;

        $results2 = Capsule::table('mod_projecttimes')
            ->where('start', '>=', $datestart)
            ->where('start', '<', $dateend)
            ->where('adminid', $adminid)
            ->get(['start', 'end']);
        foreach ($results2 as $data) {
            $starttime = $data->start;
            $endtime = $data->end;

            $time = ($endtime - $starttime);
            $duration += $time;
            $totalduration += $time;
            $daytotals[$date] += $time;
        }
        $reportdata["tablevalues"][$r][] = project_staff_logs_time($duration);

        if (str_replace('-', '', $date) == str_replace('-', '', $datetosql)) {
            break;
        }
    }

    $reportdata["tablevalues"][$r][] = '<strong>'.project_staff_logs_time($totalduration).'</strong>';

    $r++;
}

$reportdata["tablevalues"][$r][] = '<strong>Totals</strong>';

for ($i = 0; $i <= $diffInDays; $i++) {
    $date = date("Y-m-d",mktime(0,0,0,$startmonth,$startday+$i,$startyear));
    $reportdata["tablevalues"][$r][] = '<strong>'.project_staff_logs_time($daytotals[$date]).'</strong>';
    if (str_replace('-','',$date)==str_replace('-','',$datetosql)) break;
}

$total = 0;
foreach ($daytotals AS $v) $total += $v;
$reportdata["tablevalues"][$r][] = '<strong>'.project_staff_logs_time($total).'</strong>';

function project_staff_logs_time($sec, $padHours = false) {

    if($sec <= 0) { $sec = 0; } $hms = "";
    $hours = intval(intval($sec) / 3600);
    $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":" : $hours. ":";
    $minutes = intval(($sec / 60) % 60);
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
    $seconds = intval($sec % 60);
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    return $hms;

}
