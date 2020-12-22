<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Services";

$filterfields = [
    'id' => 'ID',
    'userid' => 'User ID',
    'clientname' => 'Client Name',
    'orderid' => 'Order ID',
    'packageid' => 'Product ID',
    'server' => 'Server ID',
    'regdate' => 'Registration Date',
    'domain' => 'Domain Name',
    'dedicatedip' => 'Dedicated IP',
    'assignedips' => 'Assigned IPs',
    'firstpaymentamount' => 'First Payment Amount',
    'amount' => 'Recurring Amount',
    'billingcycle' => 'Billing Cycle',
    'nextduedate' => 'Next Due Date',
    'paymentmethod' => 'Payment Method',
    'termination_date' => 'Termination Date',
    'completed_date' => 'Completed Date',
    'domainstatus' => 'Status',
    'username' => 'Username',
    'password' => 'Password',
    'notes' => 'Notes',
    'subscriptionid' => 'Subscription ID',
    'suspendreason' => 'Suspend Reason'
];

$dateRangeFields = [
    'regdate' => 'Registration Date',
    'nextduedate' => 'Next Due Date',
    'termination_date' => 'Termination Date',
    'completed_date' => 'Completed Date',
];

$removedDateRangeFields = array_diff($filterfields, $dateRangeFields);

$reportdata["description"] = $reportdata["headertext"] = '';

$incfields = $whmcs->get_req_var('incfields');
$filterfield = $whmcs->get_req_var('filterfield');
$filtertype = $whmcs->get_req_var('filtertype');
$filterq = $whmcs->get_req_var('filterq');

$regDateRange = App::getFromRequest('regDateRange');
$nextDueDateRange = App::getFromRequest('nextDueDateRange');
$termDateRange = App::getFromRequest('termDateRange');
$completedDateRange = App::getFromRequest('completedDateRange');

if (!is_array($incfields)) {
    $incfields = [];
}
if (!is_array($filterfield)) {
    $filterfield = [];
}
if (!is_array($filtertype)) {
    $filtertype = [];
}
if (!is_array($filterq)) {
    $filterq = [];
}

if (!$print) {
    $reportdata["description"] = "This report can be used to generate a custom export of"
        . " services by applying up to 5 filters. CSV Export is available via the Tools menu to the right.";

    $reportdata["headertext"] = '<form method="post" action="reports.php?report=' . $report . '">
<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
<tr><td width="20%" class="fieldlabel">Fields to Include</td><td class="fieldarea"><table width="100%"><tr>';
    $i=0;
    foreach ($filterfields as $k => $v) {
        $reportdata["headertext"] .= '<td width="20%"><input type="checkbox" name="incfields[]" value="' . $k . '" id="fd' . $k . '"';
        if (in_array($k, $incfields)) {
            $reportdata["headertext"] .= ' checked';
        }
        $reportdata["headertext"] .= ' /> <label for="fd' . $k . '">' . $v . '</label></td>';
        $i++;
        if (($i%5)==0) {
            $reportdata["headertext"] .= '</tr><tr>';
        }
    }
    $reportdata["headertext"] .= '</tr></table></td></tr>';

    for ($i = 1; $i <= 5; $i ++) {
        $reportdata["headertext"] .= '<tr><td width="20%" class="fieldlabel">Filter ' . $i . '</td><td class="fieldarea"><select name="filterfield[' . $i . ']" class="form-control select-inline"><option value="">None</option>';
        foreach ($removedDateRangeFields as $k => $v) {
            $reportdata["headertext"] .= '<option value="' . $k . '"';
            if (isset($filterfield[$i]) && $filterfield[$i] == $k) {
                $reportdata["headertext"] .= ' selected';
            }
            $reportdata["headertext"] .= '>' . $v . '</option>';
        }
        $reportdata["headertext"] .= '</select> <select name="filtertype[' . $i . ']" class="form-control select-inline">'
            . '<option value="=">Exact Match</option><option value="like"';
        if (isset($filtertype[$i]) && $filtertype[$i] == "like") {
            $reportdata["headertext"] .= ' selected';
        }
        $reportdata["headertext"] .= '>Containing</option></select>'
            . ' <input type="text" name="filterq[' . $i . ']" class="form-control input-inline input-250" value="' . (isset($filterq[$i]) ? $filterq[$i] : '') . '" /></td></tr>';
    }

    $reportdata["headertext"] .= <<<HTML
        <tr>
            <td width="20%" class="fieldlabel">Registration Date Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="regDateRange"
                           value="{$regDateRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td width="20%" class="fieldlabel">Next Due Date Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="nextDueDateRange"
                           value="{$nextDueDateRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td width="20%" class="fieldlabel">Termination Date Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="termDateRange"
                           value="{$termDateRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td width="20%" class="fieldlabel">Completed Date Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="completedDateRange"
                           value="{$completedDateRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
    </table>
    <p align="center"><input type="submit" value="Filter" class="btn btn-primary"/></p>
</form>
HTML;
}

if (count($incfields)) {
    $query = Capsule::table('tblhosting');

    foreach ($filterfield as $i => $val) {
        if ($val && array_key_exists($val, $filterfields)) {
            if ($filtertype[$i] == 'like') {
                $filterq[$i] = "%{$filterq[$i]}%";
            }
            if ($val == 'clientname') {
                $query->whereRaw(
                    "concat(tblclients.firstname, ' ', tblclients.lastname) "
                    . "{$filtertype[$i]} '{$filterq[$i]}'"
                );
            } else {
                $query->where(
                    "tblhosting.{$filterfield[$i]}",
                    $filtertype[$i],
                    $filterq[$i]
                );
            }
        }
    }

    foreach ($incfields as $fieldname) {
        if (array_key_exists($fieldname, $filterfields)) {
            $reportdata["tableheadings"][] = $filterfields[$fieldname];
            if ($fieldname == "clientname") {
                $query->addSelect(Capsule::raw("concat(tblclients.firstname, ' ', tblclients.lastname)"));
            } else {
                $query->addSelect("tblhosting.{$fieldname}");
            }
        }
    }

    if ($regDateRange) {
        $dateRange = Carbon::parseDateRangeValue($regDateRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('regdate', [$fromdate, $todate]);
    }

    if ($nextDueDateRange) {
        $dateRange = Carbon::parseDateRangeValue($nextDueDateRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('nextduedate', [$fromdate, $todate]);
    }

    if ($termDateRange) {
        $dateRange = Carbon::parseDateRangeValue($termDateRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('termination_date', [$fromdate, $todate]);
    }

    if ($completedDateRange) {
        $dateRange = Carbon::parseDateRangeValue($completedDateRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('completed_date', [$fromdate, $todate]);
    }

    $results = $query->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')->get();
    foreach ($results as $result) {
        $result = (array) $result;
        if (isset($result['paymentmethod'])) {
            $result['paymentmethod'] = $gateways->getDisplayName($result['paymentmethod']);
        }
        if (isset($result['password'])) {
            $result['password'] = decrypt($result['password']);
        }
        $reportdata["tablevalues"][] = $result;
    }
}
