<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Promotions Usage Report";
$reportdata["description"] = "This report shows usage statistics for each promotional code.";

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subDays(6)->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}

$reportdata['headertext'] = '';
if (!$print) {
    $reportdata["headertext"] = <<<EOF
<form method="post" action="reports.php?report={$report}">
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
                                   placeholder="{$optionalText}"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                {$aInt->lang('global', 'apply')}
            </button>
        </div>
    </div>
</form>
EOF;
}

$reportdata["tableheadings"] = [
    "Coupon Code",
    "Discount Type",
    "Value",
    "Recurring",
    "Notes",
    "Usage Count",
    "Total Revenue",
];

$i = 0;

$dateRange = Carbon::parseDateRangeValue($range);
$datefrom = $dateRange['from']->toDateTimeString();
$dateto = $dateRange['to']->toDateTimeString();

$results = Capsule::table('tblpromotions')
    ->orderBy('code', 'asc')
    ->get();
foreach ($results as $result) {
    $code = $result->code;
    $type = $result->type;
    $value = $result->value;
    $recurring = $result->recurring;
    $notes = $result->notes;

    $rowcount = $rowtotal = 0;

    $reportdata["drilldown"][$i]["tableheadings"] = [
        "Order ID",
        "Order Date",
        "Order Number",
        "Order Total",
        "Order Status",
    ];

    $orders = Capsule::table('tblorders')
        ->where('promocode', '=', $code)
        ->whereBetween('date', [$datefrom, $dateto])
        ->orderBy('id', 'asc')
        ->get();
    foreach ($orders as $order) {
        $orderid = $order->id;
        $ordernum = $order->ordernum;
        $orderdate = $order->date;
        $ordertotal = $order->amount;
        $orderstatus = $order->status;

        $rowcount++;
        $rowtotal += $ordertotal;

        $reportdata["drilldown"][$i]["tablevalues"][] = [
            "<a href=\"orders.php?action=view&id={$orderid}\">{$orderid}</a>",
            fromMySQLDate($orderdate),
            $ordernum,
            $ordertotal,
            $orderstatus,
        ];
    }

    $reportdata["tablevalues"][$i] = [
        $code,
        $type,
        $value,
        $recurring,
        $notes,
        $rowcount,
        format_as_currency($rowtotal),
    ];

    $i++;
}
