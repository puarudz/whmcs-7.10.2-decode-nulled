<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;
use WHMCS\Utility\GeoIp;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Ticket Feedback Comments";
$reportdata["description"] = "This report allows you to review feedback comments submitted by customers.";

$staffid = App::getFromRequest('staffid');
$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subDays(6)->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}

$module = App::getFromRequest('module');
$moduleString = '';
if ($module) {
    $moduleString = 'module=' . $module . '&';
}

$admins = Capsule::table('tbladmins')
    ->orderBy('firstname')
    ->pluck(
        Capsule::raw(
            'CONCAT_WS(\' \', tbladmins.firstname, tbladmins.lastname) as name'
        ),
        'id'
    );

$adminDropdown = '';
foreach ($admins as $adminId => $adminName) {
    $selected = '';
    if ($adminId == $staffid) {
        $selected = ' selected="selected"';
    }
    $adminDropdown .= "<option value=\"{$adminId}\"{$selected}>{$adminName}</option>";
}

$reportdata['headertext'] = '';
if (!$print) {
    $reportdata['headertext'] = <<<HTML
<form method="post" action="?{$moduleString}report={$report}&currencyid={$currencyid}&calculate=true">
    <div class="report-filters-wrapper">
        <div class="inner-container">
            <h3>Filters</h3>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputFilterStaff">Staff Name</label>
                        <select id="inputFilterStaff" name="staffid" class="form-control">
                            <option value="0">- Any -</option>
                            {$adminDropdown}
                        </select>
                    </div>
                </div>
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

$reportdata["tableheadings"][] = "Ticket ID";
$reportdata["tableheadings"][] = "Staff Name";
$reportdata["tableheadings"][] = "Subject";
$reportdata["tableheadings"][] = "Feedback Left";
$reportdata["tableheadings"][] = "Rating";
$reportdata["tableheadings"][] = "Comments";
$reportdata["tableheadings"][] = "IP Address";

$dateRange = Carbon::parseDateRangeValue($range);
$fromdate = $dateRange['from']->toDateTimeString();
$todate = $dateRange['to']->endOfDay()->toDateTimeString();

$query = Capsule::table('tblticketfeedback')
    ->where('datetime', '>=', $fromdate)
    ->where('datetime', '<=', $todate)
    ->join(
        'tbladmins',
        'tbladmins.id',
        '=',
        'tblticketfeedback.adminid'
    )
    ->leftJoin(
        'tbltickets',
        'tbltickets.id',
        '=',
        'tblticketfeedback.ticketid'
    );
if ($staffid) {
    $query = $query->where('adminid', (int) $staffid);
}
$query->orderBy('datetime')
    ->select(
        [
            'tblticketfeedback.*',
            Capsule::raw('CONCAT(tbladmins.firstname, \' \', tbladmins.lastname) as adminname'),
            Capsule::raw('CONCAT(tid, \'|||\', title) as ticketinfo')

        ]
    );
$ticketUrl = 'supporttickets.php?action=viewticket&id=';
foreach ($query->get() as $data) {
    $data = (array) $data;
    $id = $data['id'];
    $ticketid = $data['ticketid'];
    $ticketinfo = $data['ticketinfo'];
    $adminid = $data['adminid'];
    $adminname = $data['adminname'];
    $rating = $data['rating'];
    $comments = $data['comments'];
    $datetime = $data['datetime'];
    $ip = $data['ip'];

    if ($adminid == 0) {
        $adminname = 'Generic Feedback';
    } elseif (!trim($adminname)) {
        $adminname = 'Deleted Admin';
    }

    if (!trim($comments)) {
        $comments = 'No Comments Left';
    }

    $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $datetime)
        ->toAdminDateTimeFormat();

    $subject = '';
    $tickettid = 'Not Found';
    if ($ticketinfo) {
        $ticketinfo = explode('|||', $ticketinfo);
        $tickettid = $ticketinfo[0];
        $subject = $ticketinfo[1];
        $tickettid = "<a href=\"{$ticketUrl}{$ticketid}\" target=\"_blank\">{$tickettid}</a>";
    }

    $reportdata["tablevalues"][] = [
        $tickettid,
        $adminname,
        $subject,
        $datetime,
        $rating,
        nl2br($comments),
        GeoIp::getLookupHtmlAnchor($ip),
    ];
}
