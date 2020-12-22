<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Invoices";

$filterfields = [
    'id' => 'ID',
    'userid' => 'User ID',
    'clientname' => 'Client Name',
    'invoicenum' => 'Invoice Number',
    'date' => 'Creation Date',
    'duedate' => 'Due Date',
    'datepaid' => 'Date Paid',
    'date_refunded' => 'Date Refunded',
    'date_cancelled' => 'Date Cancelled',
    'subtotal' => 'Subtotal',
    'credit' => 'Credit',
    'tax' => 'Tax',
    'tax2' => 'Tax2',
    'total' => 'Total',
    'taxrate' => 'Tax Rate',
    'taxrate2' => 'Tax Rate 2',
    'status' => 'Status',
    'paymentmethod' => 'Payment Method',
    'notes' => 'Notes',
];

$dateRangeFields = [
    'date' => 'Creation Date',
    'duedate' => 'Due Date',
    'datepaid' => 'Date Paid',
    'date_refunded' => 'Date Refunded',
    'date_cancelled' => 'Date Cancelled',
];

$removedDateRangeFields = array_diff($filterfields, $dateRangeFields);

$reportdata["description"] = $reportdata["headertext"] = '';

$incfields = $whmcs->get_req_var('incfields');
$filterfield = $whmcs->get_req_var('filterfield');
$filtertype = $whmcs->get_req_var('filtertype');
$filterq = $whmcs->get_req_var('filterq');

$createDateRange = App::getFromRequest('createDateRange');
$dueDateRange = App::getFromRequest('dueDateRange');
$datePaidRange = App::getFromRequest('datePaidRange');
$dateRefundedRange = App::getFromRequest('dateRefundedRange');
$dateCancelledRange = App::getFromRequest('dateCancelledRange');

if (!is_array($incfields)) {
    $incfields = array();
}
if (!is_array($filterfield)) {
    $filterfield = array();
}
if (!is_array($filtertype)) {
    $filtertype = array();
}
if (!is_array($filterq)) {
    $filterq = array();
}

if (!$print) {
    $reportdata["description"] = "This report can be used to generate a custom export of"
        . " invoices by applying up to 5 filters. CSV Export is available via the Tools menu to the right.";

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
        if (($i%5) == 0) {
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
            <td width="20%" class="fieldlabel">Creation Date Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="createDateRange"
                           value="{$createDateRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td width="20%" class="fieldlabel">Due Date Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="dueDateRange"
                           value="{$dueDateRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td width="20%" class="fieldlabel">Date Paid Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="datePaidRange"
                           value="{$datePaidRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td width="20%" class="fieldlabel">Date Refunded Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="dateRefundedRange"
                           value="{$dateRefundedRange}"
                           class="form-control date-picker-search"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td width="20%" class="fieldlabel">Date Cancelled Range</td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                           type="text"
                           name="dateCancelledRange"
                           value="{$dateCancelledRange}"
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
    $query = Capsule::table('tblinvoices');
    
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
                    "tblinvoices.{$filterfield[$i]}",
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
                $query->addSelect("tblinvoices.{$fieldname}");
            }
        }
    }

    if ($createDateRange) {
        $dateRange = Carbon::parseDateRangeValue($createDateRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('date', [$fromdate, $todate]);
    }

    if ($dueDateRange) {
        $dateRange = Carbon::parseDateRangeValue($dueDateRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('duedate', [$fromdate, $todate]);
    }

    if ($datePaidRange) {
        $dateRange = Carbon::parseDateRangeValue($datePaidRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('datepaid', [$fromdate, $todate]);
    }

    if ($dateRefundedRange) {
        $dateRange = Carbon::parseDateRangeValue($dateRefundedRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('date_refunded', [$fromdate, $todate]);
    }

    if ($dateCancelledRange) {
        $dateRange = Carbon::parseDateRangeValue($dateCancelledRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('date_cancelled', [$fromdate, $todate]);
    }

    $results = $query->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')->get();

    foreach ($results as $result) {
        $result = (array) $result;
        if (isset($result['paymentmethod'])) {
            $result['paymentmethod'] = $gateways->getDisplayName($result['paymentmethod']);
        }
        $reportdata["tablevalues"][] = $result;
    }
}
