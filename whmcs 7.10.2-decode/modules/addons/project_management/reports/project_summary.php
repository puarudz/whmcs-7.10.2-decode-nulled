<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require(ROOTDIR."/includes/clientfunctions.php");

$reportdata["title"] = "Project Management Summary";
$reportdata["description"] = "This report shows a summary of all projects with times logged betwen";

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subDays(6)->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}

$statusdropdown = '<select name="status" class="form-control"><option value="">- Any -</option>';
$statuses = Capsule::table('tbladdonmodules')
    ->where('module', 'project_management')
    ->where('setting', 'statusvalues')
    ->value('value');
$statuses = explode(",", $statuses);
foreach ($statuses as $statusx) {
    $statusx = explode("|", $statusx, 2);
    $statusdropdown .= '<option';
    if ($statusx[0] == $status) {
        $statusdropdown .= ' selected';
    }
    $statusdropdown .= '>' . $statusx[0] . '</option>';
}
$statusdropdown .= '</select>';

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
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputFilterStaff">Status</label>
                        {$statusdropdown}
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputFilterStaff">Staff Member</label>
                        <select id="inputFilterStaff" name="adminid" class="form-control">
                            <option value="0">- Any -</option>
                            {$adminDropdown}
                        </select>
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

$reportdata["tableheadings"] = array("ID","Created","Project Title","Assigned Staff","Associated Client","Due Date","Total Invoiced","Total Paid","Total Time","Status");

$totalprojectstime = $i = 0;

$dateRange = Carbon::parseDateRangeValue($range);
$fromdate = $dateRange['from']->toDateTimeString();
$todate = $dateRange['to']->toDateTimeString();

$results = Capsule::table(mod_project)
    ->whereBetween('duedate', [$fromdate, $todate]);
if ($adminid) {
    $results->where('adminid', (int) $adminid);
}
if ($status) {
    $results->where('status', db_escape_string($status));
}
$results = $results->get();
foreach ($results as $data) {
    $totaltaskstime = 0;
    $projectid = $data->id;
    $projectname = $data->title;
    $adminid = $data->adminid;
    $userid = $data->userid;
    $created = $data->created;
    $duedate = $data->duedate;
    $ticketids = explode(',', $data->ticketids);
    $projectstatus = $data->status;

    $created = fromMySQLDate($created);
    $duedate = fromMySQLDate($duedate);

    $admin = ($adminid) ? getAdminName($adminid) : 'None';

    if ($userid) {
        $clientsdetails = getClientsDetails($userid);
        $client = '<a href="clientssummary.php?userid='.$clientsdetails['userid'].'">'.$clientsdetails['firstname'].' '.$clientsdetails['lastname'];
        if($clientsdetails['companyname']) $client .= ' ('.$clientsdetails['companyname'].')';
        $client .= '</a>';
        $currency = getCurrency();
    } else {
        $client = 'None';
    }

    $ticketinvoicelinks = Capsule::table('tbltickets')
        ->whereIn('tid', $ticketids)
        ->pluck('tid');
    $baseQuery = Capsule::table('tblinvoices')
        ->join('tblinvoiceitems', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
        ->where('tblinvoiceitems.description', 'like', "%Project #{$projectid}%")
        ->orWhere(function ($query) use ($projectid, $ticketinvoicelinks) {
            $query->orWhere(function ($query) use ($projectid) {
                $query->where('tblinvoiceitems.type', 'Project')
                    ->where('tblinvoiceitems.relid', $projectid);
            });
            $query->orWhere(function ($query) use ($ticketinvoicelinks) {
                foreach ($ticketinvoicelinks as $ticketinvoicelink) {
                    $query->orWhere('tblinvoiceitems.description', 'like', "%Ticket #{$ticketinvoicelink}%");
                }
            });
        });
    $totalinvoiced = $baseQuery->value(
        Capsule::raw('sum(tblinvoices.subtotal + tblinvoices.tax + tblinvoices.tax2)')
    );
    $totalinvoiced = ($userid) ? formatCurrency($totalinvoiced) : format_as_currency($totalinvoiced);

    $baseQuery->where('tblinvoices.status', 'Paid');
    $totalpaid = $baseQuery->value(
        Capsule::raw('sum(tblinvoices.subtotal + tblinvoices.tax + tblinvoices.tax2)')
    );
    $totalpaid = ($userid) ? formatCurrency($totalpaid) : format_as_currency($totalpaid);

    $reportdata["drilldown"][$i]["tableheadings"] = array("Task Name","Start Time","Stop Time","Duration","Task Status");

    $timerresults = Capsule::table('mod_projecttimes')
        ->where('mod_projecttimes.projectid', $projectid)
        ->join('mod_projecttasks', 'mod_projecttimes.taskid', '=', 'mod_projecttasks.id')
        ->get([
            'mod_projecttimes.start',
            'mod_projecttimes.end',
            'mod_projecttasks.task',
            'mod_projecttasks.completed',
        ]);
    foreach ($timerresults as $data2) {
        $rowcount = $rowtotal = 0;

        $taskid = $data2->id;
        $task = $data2->task;
        $taskadminid = $data2->adminid;
        $timerstart = $data2->start;
        $timerend = $data2->end;
        $duration = ($timerend) ? ($timerend-$timerstart) : 0;

        $taskadmin = getAdminName($taskadminid);
        $starttime = date("d/m/Y H:i:s ",$timerstart);
        $stoptime = date("d/m/Y H:i:s ",$timerend);

        $taskstatus = "Open";
        if ($data2->completed) {
            $taskstatus = "Completed";
        }

        $totalprojectstime += $duration;
        $totaltaskstime += $duration;

        $rowcount++;
        $rowtotal += $ordertotal;

        $reportdata["drilldown"][$i]["tablevalues"][] = array($task,$starttime,$stoptime,project_management_sec2hms($duration),$taskstatus);
    }

    $reportdata["tablevalues"][$i] = array('<a href="addonmodules.php?module=project_management&m=view&projectid='.$projectid.'">'.$projectid.'</a>',$created,$projectname,$admin,$client,$duedate,$totalinvoiced,$totalpaid,project_management_sec2hms($totaltaskstime),$projectstatus);

    $i++;


}

$reportdata["footertext"] = "Total Time effort across $i projects: ".project_management_sec2hms($totalprojectstime);

function project_management_sec2hms ($sec, $padHours = false){

    if ($sec <= 0) {
        $sec = 0;
    }
    $hms = "";
    $hours = intval(intval($sec) / 3600);
    $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":" : $hours. ":";
    $minutes = intval(($sec / 60) % 60);
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT);

    return $hms;

}
