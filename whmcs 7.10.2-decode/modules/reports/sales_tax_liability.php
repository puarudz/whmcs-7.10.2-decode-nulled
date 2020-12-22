<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Sales Tax Liability";
$reportdata["description"] = "This report shows sales tax liability for the selected period";

$reportdata["currencyselections"] = true;

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subDays(6)->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}
$currencyID = (int) $currencyid;

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

if ($calculate) {
    $dateRange = Carbon::parseDateRangeValue($range);
    $queryStartDate = $dateRange['from']->toDateTimeString();
    $queryEndDate = $dateRange['to']->toDateTimeString();

    $result = Capsule::table('tblinvoices')
        ->select(
            Capsule::raw('count(*) as `count`'),
            Capsule::raw('sum(total) as `total`'),
            Capsule::raw('sum(tblinvoices.credit) as `credit`'),
            Capsule::raw('sum(tax) as `tax`'),
            Capsule::raw('sum(tax2) as `tax2`')
        )
        ->distinct()
        ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
        ->leftJoin('tblinvoiceitems', function ($join) {
            $join->on('tblinvoiceitems.invoiceid', '=', 'tblinvoices.id');
            $join->on(function ($join) {
                $join
                    ->on('tblinvoiceitems.type', '=', Capsule::raw('"Add Funds"'))
                    ->orOn('tblinvoiceitems.type', '=', Capsule::raw('"Invoice"'));
            });
        })
        ->whereBetween('tblinvoices.datepaid', [$queryStartDate, $queryEndDate])
        ->where('tblinvoices.status', '=', 'Paid')
        ->where('tblclients.currency', '=', $currencyID)
        ->whereNull('tblinvoiceitems.id')
        ->first();

    $numinvoices = $result->count;
    $total = ($result->total + $result->credit);
    $tax = $result->tax;
    $tax2 = $result->tax2;

    if (!$total) $total="0.00";
    if (!$tax) $tax="0.00";
    if (!$tax2) $tax2="0.00";

    $reportdata["headertext"] .= "<br>$numinvoices Invoices Found<br><B>Total Invoiced:</B> ".formatCurrency($total)." &nbsp; <B>Tax Level 1 Liability:</B> ".formatCurrency($tax)." &nbsp; <B>Tax Level 2 Liability:</B> ".formatCurrency($tax2);
}

$reportdata["headertext"] .= "</center>";

$reportdata["tableheadings"] = array(
    $aInt->lang('fields', 'invoiceid'),
    $aInt->lang('fields', 'clientname'),
    $aInt->lang('fields', 'invoicedate'),
    $aInt->lang('fields', 'datepaid'),
    $aInt->lang('fields', 'subtotal'),
    $aInt->lang('fields', 'tax'),
    $aInt->lang('fields', 'credit'),
    $aInt->lang('fields', 'total'),
);

$results = Capsule::table('tblinvoices')
    ->select('tblinvoices.*', 'tblclients.firstname', 'tblclients.lastname')
    ->distinct()
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->leftJoin('tblinvoiceitems', function ($join) {
        $join->on('tblinvoiceitems.invoiceid', '=', 'tblinvoices.id');
        $join->on(function ($join) {
            $join
                ->on('tblinvoiceitems.type', '=', Capsule::raw('"Add Funds"'))
                ->orOn('tblinvoiceitems.type', '=', Capsule::raw('"Invoice"'));
        });
    })
    ->whereBetween('tblinvoices.datepaid', [$queryStartDate, $queryEndDate])
    ->where('tblinvoices.status', '=', 'Paid')
    ->where('tblclients.currency', '=', $currencyID)
    ->whereNull('tblinvoiceitems.id')
    ->orderBy('date', 'asc')
    ->get();

foreach ($results as $result) {
    $id = $result->id;
    $userid = $result->userid;
    $client = "{$result->firstname} {$result->lastname}";
    $date = fromMySQLDate($result->date);
    $datepaid = fromMySQLDate($result->datepaid);
    $currency = getCurrency($userid);
    $subtotal = $result->subtotal;
    $credit = $result->credit;
    $tax = ($result->tax + $result->tax2);
    $total = ($result->total + $credit);
    $reportdata["tablevalues"][] = [
        "{$id}",
        "{$client}",
        "{$date}",
        "{$datepaid}",
        "{$subtotal}",
        "{$tax}",
        "{$credit}",
        "{$total}",
    ];
}

$data["footertext"]="This report excludes invoices that affect a clients credit balance "
    . "since this income will be counted and reported when it is applied to invoices for products/services.";
