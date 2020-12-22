<?php

use WHMCS\Billing\Currency;
use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Transactions";

$filterfields = [
    'id' => 'ID',
    'userid' => 'User ID',
    'clientname' => 'Client Name',
    'currency' => 'Currency',
    'gateway' => 'Payment Method',
    'date' => 'Date',
    'description' => 'Description',
    'invoiceid' => 'Invoice ID',
    'transid' => 'Transaction ID',
    'amountin' => 'Amount In',
    'fees' => 'Fees',
    'amountout' => 'Amount Out',
    'rate' => 'Exchange Rate',
    'refundid' => 'Refund ID'
];

$dateRangeFields = [
    'date' => 'Date',
];

$removedDateRangeFields = array_diff($filterfields, $dateRangeFields);

$reportdata["description"] = $reportdata["headertext"] = '';

$incfields = $whmcs->get_req_var('incfields');
$filterfield = $whmcs->get_req_var('filterfield');
$filtertype = $whmcs->get_req_var('filtertype');
$filterq = $whmcs->get_req_var('filterq');

$range = App::getFromRequest('range');

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
        . " transactions by applying up to 5 filters. CSV Export is available via the"
        . " Tools menu to the right.";

    $reportdata["headertext"] = '<form method="post" action="reports.php?report=' . $report . '">
<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
<tr><td width="20%" class="fieldlabel">Fields to Include</td><td class="fieldarea"><table width="100%"><tr>';
    $i=0;
    foreach ($filterfields as $k => $v) {
        $reportdata["headertext"] .= '<td width="20%"><input type="checkbox" name="incfields[]" value="' . $k . '" id="fd' . $k . '"';
        if (in_array($k, $incfields)) {
            $reportdata["headertext"] .= ' checked';
        }
        $reportdata["headertext"] .= ' /> <label for="fd' . $k . '">'.$v.'</label></td>';
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
            if (isset($filterfield[$i]) && $filterfield[$i]==$k) {
                $reportdata["headertext"] .= ' selected';
            }
            $reportdata["headertext"] .= '>' . $v . '</option>';
        }
        $reportdata["headertext"] .= '</select> <select name="filtertype[' . $i . ']" class="form-control select-inline">'
            . '<option value="=">Exact Match</option><option value="like"';
        if (isset($filtertype[$i]) && $filtertype[$i]=="like") {
            $reportdata["headertext"] .= ' selected';
        }
        $reportdata["headertext"] .= '>Containing</option></select>'
            . ' <input type="text" name="filterq[' . $i . ']" class="form-control select-inline input-250" value="' . (isset($filterq[$i]) ? $filterq[$i] : '') . '" /></td></tr>';
    }

    $reportdata["headertext"] .= <<<HTML
        <tr>
            <td width="20%" class="fieldlabel">Date Range</td>
            <td class="fieldarea">
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
            </td>
        </tr>
    </table>
    <p align="center"><input type="submit" value="Filter" class="btn btn-primary"/></p>
</form>
HTML;
}

if (count($incfields)) {
    $query = Capsule::table('tblaccounts');

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
            } elseif ($val == 'currency') {
                $currencyCode = strtoupper(trim($filterq[$i]));
                $currencyId = Currency::where('code', $currencyCode)
                    ->value('id');
                $query->where(function ($query) use ($currencyId) {
                    return $query->where('tblclients.currency', (int) $currencyId)
                        ->orWhere(function ($query) use ($currencyId) {
                            return $query->where('tblaccounts.userid', 0)
                                ->where('tblaccounts.currency', (int) $currencyId);
                        });
                });
            } else {
                $query->where(
                    "tblaccounts.{$filterfield[$i]}",
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
                $query->addSelect("tblaccounts.{$fieldname}");
            }
        }
    }

    if (in_array('currency', $incfields) && !in_array('userid', $incfields)) {
        $query->addSelect('tblaccounts.userid');
    }

    if ($range) {
        $dateRange = Carbon::parseDateRangeValue($range);
        $fromdate = $dateRange['from']->toDateTimeString();
        $todate = $dateRange['to']->toDateTimeString();
        $query->whereBetween('date', [$fromdate, $todate]);
    }

    $results = $query->leftJoin('tblclients', 'tblclients.id', '=', 'tblaccounts.userid')
        ->orderBy('date', 'asc')
        ->get();
    foreach ($results as $result) {
        $result = (array) $result;
        if (isset($result['currency'])) {
            $currency = getCurrency($result['userid'], $result['currency']);
            $result['currency'] = $currency['code'];

            if (!in_array('userid', $incfields)) {
                unset($result['userid']);
            }
        }
        if (isset($result['gateway'])) {
            $result['gateway'] = $gateways->getDisplayName($result['gateway']);
        }
        $reportdata["tablevalues"][] = $result;
    }
}
