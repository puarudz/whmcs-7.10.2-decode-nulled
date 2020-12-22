<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Clients";

$filterfields = [
    'id' => 'ID',
    'firstname' => 'First Name',
    'lastname' => 'Last Name',
    'companyname' => 'Company Name',
    'email' => 'Email',
    'address1' => 'Address 1',
    'address2' => 'Address 2',
    'city' => 'City',
    'state' => 'State',
    'postcode' => 'Postcode',
    'country' => 'Country',
    'phonenumber' => 'Phone Number',
    'currency' => 'Currency',
    'groupid' => 'Client Group ID',
    'credit' => 'Credit',
    'datecreated' => 'Creation Date',
    'notes' => 'Notes',
    'status' => 'Status'
];

$dateRangeFields = [
    'datecreated' => 'Creation Date',
];

$removedDateRangeFields = array_diff($filterfields, $dateRangeFields);

$reportdata["description"] = $reportdata["headertext"] = '';

$incfields = $whmcs->get_req_var('incfields');
$filterfield = $whmcs->get_req_var('filterfield');
$filtertype = $whmcs->get_req_var('filtertype');
$filterq = $whmcs->get_req_var('filterq');

$createDateRange = App::getFromRequest('createDateRange');

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
    $reportdata["description"] = "This report can be used to generate a custom export"
        . " of clients by applying up to 5 filters. CSV Export is available via the Tools menu to the right.";

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
            $reportdata["headertext"] .= '<option value="'.$k.'"';
            if (isset($filterfield[$i]) && $filterfield[$i]==$k) {
                $reportdata["headertext"] .= ' selected';
            }
            $reportdata["headertext"] .= '>'.$v.'</option>';
        }
        $reportdata["headertext"] .= '</select> <select name="filtertype[' . $i . ']" class="form-control select-inline">';
        $reportdata["headertext"] .= '<option value="=">Exact Match</option><option value="like"';
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
    </table>
    <p align="center"><input type="submit" value="Filter" class="btn btn-primary"/></p>
</form>
HTML;
}

if (count($incfields)) {
    $filters = [];
    foreach ($filterfield as $i => $val) {
        if ($val && array_key_exists($val, $filterfields)) {
            if ($filtertype[$i] === 'like') {
                $filterq[$i] = '%' . $filterq[$i] . '%';
            }
            $filters[] = [
                "name" => $filterfield[$i],
                "operator" => $filtertype[$i],
                "value" => $filterq[$i],
            ];
        }
    }

    $fieldlist = [];
    foreach ($incfields as $fieldname) {
        if (array_key_exists($fieldname, $filterfields)) {
            $reportdata["tableheadings"][] = $filterfields[$fieldname];
            $fieldlist[] = $fieldname;
        }
    }

    $query = Capsule::table('tblclients')
        ->addSelect($fieldlist)
        ->where($filters);

    if ($createDateRange) {
        $dateRange = Carbon::parseDateRangeValue($createDateRange);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('datecreated', [$fromdate, $todate]);
    }

    $results = $query->get();

    foreach ($results as $result) {
        $result = (array) $result;
        if (isset($result['currency'])) {
            $result['currency'] = Capsule::table('tblcurrencies')
                ->where('id', '=', $result['currency'])
                ->value('code');
        }
        $reportdata["tablevalues"][] = $result;
    }
}
