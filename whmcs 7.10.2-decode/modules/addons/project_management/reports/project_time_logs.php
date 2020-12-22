<?php

use Illuminate\Database\Query\Builder;
use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Project Management Project Time Logs";
$reportdata["description"] = "This report shows the amount of time logged on a per task basis, per staff member, for a given date range.";

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subDays(6)->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
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

$reportdata["tableheadings"] = array(
    "Project Name",
    "Task Name",
    "Total Time"
);

$i = 0;
$dateRange = Carbon::parseDateRangeValue($range);
$datefrom = $dateRange['from']->timestamp;
$dateto = $dateRange['to']->timestamp;

$adminquery = ($adminid) ? " AND adminid='".(int)$adminid."'" : '';
$results = Capsule::table('tbladmins')
    ->orderBy('firstname', 'asc')
    ->get(['id', 'firstname', 'lastname']);
foreach ($results as $data) {
    $adminid = $data->id;
    $adminfirstname = $data->firstname;
    $adminlastname = $data->lastname;

    $projectData = Capsule::table('mod_projecttimes')
        ->where(function (Builder $query) use ($datefrom, $dateto) {
            $query->where('mod_projecttimes.start', '>=', $datefrom)
                ->where('mod_projecttimes.end', '<=', $dateto);
        })
        ->where('mod_projecttimes.adminid', $adminid)
        ->join('mod_project', 'mod_project.id', '=', 'mod_projecttimes.projectid')
        ->join('mod_projecttasks', 'mod_projecttasks.id', '=', 'mod_projecttimes.taskid')
        ->orderBy('start')
        ->get(
            [
                'mod_project.id',
                'mod_projecttimes.start',
                'mod_projecttimes.end',
                'mod_project.title',
                'mod_project.created',
                'mod_project.duedate',
                'mod_projecttasks.task',
            ]
        );

    $projectTimes = [];
    foreach ($projectData as $data) {
        $projectid = $data->id;
        $projecttitle = $data->title;
        $projecttask = $data->task;
        $projectCreated = $data->created;
        $projectDueDate = $data->duedate;
        if ($projectCreated != '0000-00-00') {
            $projectCreated = Carbon::createFromFormat('Y-m-d', $projectCreated)
                ->toAdminDateFormat();
        }
        if ($projectDueDate != '0000-00-00') {
            $projectDueDate = Carbon::createFromFormat('Y-m-d', $projectDueDate)
                ->toAdminDateFormat();
        }
        if ($projectDueDate == '0000-00-00') {
            $projectDueDate = 'N/A';
        }

        $time = ($data->end - $data->start);

        $projectTimes[$projectid][] = [
            'projectName' => $projecttitle,
            'projectTask' => $projecttask,
            'time' => $time,
            'projectStart' => $projectCreated,
            'projectDueDate' => $projectDueDate,
        ];
    }
    $reportdata["tablevalues"][$i] = array("**<strong>$adminfirstname $adminlastname</strong>");
    $i++;
    if (count($projectTimes) === 0) {
        $reportdata["tablevalues"][$i] = array(
            '',
            '',
            '<strong>0:00:00</strong>'
        );
        $i++;
        continue;
    }

    $totalduration = 0;
    foreach ($projectTimes as $projectId => $projectData) {
        $link = '<a href="addonmodules.php?module=project_management&m=view&projectid='.$projectId.'">'
            . $projectData[0]['projectName']
            . '</a>';
        $projectHead = "<div class=\"row\">
    <div class=\"col-sm-3\">{$link}</div>
    <div class=\"col-sm-3\">Created: {$projectData[0]['projectStart']}</div>
    <div class=\"col-sm-3\">Due Date: {$projectData[0]['projectDueDate']}</div>
</div>";

        $reportdata["tablevalues"][$i] = array("+*{$projectHead}");

        $i++;
        foreach ($projectData as $data) {
            $totalduration += $data['time'];
            $reportdata["tablevalues"][$i] = array(
                '',
                $data['projectTask'],
                project_task_logs_time($data['time'])
            );

            $i++;
        }
        $reportdata["tablevalues"][$i] = array(
            '',
            '',
            '<strong>' . project_task_logs_time($totalduration) . '</strong>'
        );
        $i++;
    }
}

function project_task_logs_time ($sec, $padHours = false){

    if($sec <= 0) { $sec = 0; } $hms = "";
    $hours = intval(intval($sec) / 3600);
    $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":" : $hours. ":";
    $minutes = intval(($sec / 60) % 60);
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
    $seconds = intval($sec % 60);
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    return $hms;

}
