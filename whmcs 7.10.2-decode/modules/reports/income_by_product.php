<?php

use Illuminate\Database\Query\Builder;
use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$pmonth = str_pad((int)$month, 2, "0", STR_PAD_LEFT);

$reportdata["title"] = "Income by Product for ".$months[(int)$month]." ".$year;
$reportdata["description"] = "This report provides a breakdown per product/service of invoices paid in a given month. Please note this excludes overpayments & other payments made to deposit funds (credit), and includes invoices paid from credit added in previous months, and thus may not match the income total for the month.";
$reportdata["currencyselections"] = true;

$reportdata["tableheadings"] = array("Product Name","Units Sold","Value");

$products = $addons = array();
$dateRange = Carbon::create(
    $year,
    $month,
    1
);

# Loop Through Products
$result = Capsule::table('tblinvoiceitems')
    ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
    ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->whereBetween(
        'tblinvoices.datepaid',
        [
            $dateRange->startOfMonth()->toDateTimeString(),
            $dateRange->endOfMonth()->toDateTimeString(),
        ]
    )
    ->where(function (Builder $query) {
        $query->where('tblinvoiceitems.type', 'Hosting')
            ->orWhere('tblinvoiceitems.type', 'Setup')
            ->orWhere('tblinvoiceitems.type', 'like', 'ProrataProduct%');
    })
    ->where('currency', $currencyid)
    ->groupBy('tblhosting.packageid')
    ->select(
        [
            Capsule::raw('tblhosting.packageid as packageId'),
            Capsule::raw('COUNT(*) as unitsSold'),
            Capsule::raw('SUM(tblinvoiceitems.amount) as amount')
        ]
    )->get();

foreach ($result as $data) {
    $products[$data->packageId] = [
        'amount' => $data->amount,
        'unitssold' => $data->unitsSold,
    ];
}

$result = Capsule::table('tblinvoiceitems')
    ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
    ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->whereBetween(
        'tblinvoices.datepaid',
        [
            $dateRange->startOfMonth()->toDateTimeString(),
            $dateRange->endOfMonth()->toDateTimeString(),
        ]
    )
    ->where('tblinvoiceitems.type', 'PromoHosting')
    ->where('currency', $currencyid)
    ->groupBy('tblhosting.packageid')
    ->select(
        [
            Capsule::raw('tblhosting.packageid as packageId'),
            Capsule::raw('COUNT(*) as unitsSold'),
            Capsule::raw('SUM(tblinvoiceitems.amount) as amount')
        ]
    )
    ->get();

foreach ($result as $data) {
    $products[$data->packageId]["amount"] += $data->amount;
}

# Loop Through Addons
$result = Capsule::table('tblinvoiceitems')
    ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
    ->join('tblhostingaddons', 'tblhostingaddons.id', '=', 'tblinvoiceitems.relid')
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->whereBetween(
        'tblinvoices.datepaid',
        [
            $dateRange->startOfMonth()->toDateTimeString(),
            $dateRange->endOfMonth()->toDateTimeString(),
        ]
    )
    ->where('tblinvoiceitems.type', 'Addon')
    ->where('currency', $currencyid)
    ->groupBy('tblhostingaddons.addonid')
    ->select(
        [
            Capsule::raw('tblhostingaddons.addonid as addonId'),
            Capsule::raw('COUNT(*) as unitsSold'),
            Capsule::raw('SUM(tblinvoiceitems.amount) as amount')
        ]
    )->get();

foreach ($result as $data) {
    $addons[$data->addonId] = [
        'amount' => $data->amount,
        'unitssold' => $data->unitsSold,
    ];
}

$total = 0;
$itemtotal = 0;
$firstdone = false;
$result = Capsule::table('tblproducts')
    ->join(
        'tblproductgroups',
        'tblproductgroups.id',
        '=',
        'tblproducts.gid'
    )
    ->orderBy('tblproductgroups.order')
    ->orderBy('tblproducts.order')
    ->orderBy('tblproducts.name')
    ->get(
        [
            'tblproducts.id',
            'tblproducts.name',
            Capsule::raw('`tblproductgroups`.`name` as groupname')
        ]
    );
foreach ($result as $data) {
    $pid = $data->id;
    $group = $data->groupname;
    $prodname = $data->name;

    if ($group!=$prevgroup) {
        $total += $itemtotal;
        if ($firstdone) {
            $reportdata["tablevalues"][] = array('','<strong>Sub-Total</strong>','<strong>'.formatCurrency($itemtotal).'</strong>');
            $chartdata['rows'][] = array('c'=>array(array('v'=>$prevgroup),array('v'=>$itemtotal,'f'=>formatCurrency($itemtotal))));
        }
        $reportdata["tablevalues"][] = array("**<strong>$group</strong>");
        $itemtotal = 0;
    }

    $amount = $products[$pid]["amount"];
    $number = $products[$pid]["unitssold"];

    $itemtotal += $amount;

    if (!$amount) $amount="0.00";
    if (!$number) $number="0";
    $amount = formatCurrency($amount);

    $reportdata["tablevalues"][] = array($prodname,$number,$amount);

    $prevgroup = $group;
    $firstdone = true;

}

$total += $itemtotal;
$reportdata["tablevalues"][] = array('','<strong>Sub-Total</strong>','<strong>'.formatCurrency($itemtotal).'</strong>');
$chartdata['rows'][] = array('c'=>array(array('v'=>$group),array('v'=>$itemtotal,'f'=>formatCurrency($itemtotal))));

$reportdata["tablevalues"][] = array("**<strong>Addons</strong>");

$itemtotal = 0;
$result = Capsule::table('tbladdons')
    ->orderBy('name')
    ->get(
        [
            'id',
            'name',
        ]
    );
foreach ($result as $data) {
    $addonid = $data->id;
    $prodname = $data->name;

    $amount = $addons[$addonid]["amount"];
    $number = $addons[$addonid]["unitssold"];

    $itemtotal += $amount;

    if (!$amount) $amount="0.00";
    if (!$number) $number="0";
    $amount = formatCurrency($amount);

    $reportdata["tablevalues"][] = array($prodname,$number,$amount);

    $prevgroup = $group;

}

$itemtotal += $addons[0]["amount"];
$number = $addons[0]["unitssold"];
$amount = $addons[0]["amount"];
if (!$amount) $amount="0.00";
if (!$number) $number="0";
$reportdata["tablevalues"][] = array('Miscellaneous Custom Addons',$number,formatCurrency($amount));

$total += $itemtotal;
$reportdata["tablevalues"][] = array('','<strong>Sub-Total</strong>','<strong>'.formatCurrency($itemtotal).'</strong>');
$chartdata['rows'][] = array('c'=>array(array('v'=>"Addons"),array('v'=>$itemtotal,'f'=>formatCurrency($itemtotal))));

$itemtotal = 0;
$reportdata["tablevalues"][] = array("**<strong>Miscellaneous</strong>");

$data = Capsule::table('tblinvoiceitems')
    ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->whereBetween(
        'tblinvoices.datepaid',
        [
            $dateRange->startOfMonth()->toDateTimeString(),
            $dateRange->endOfMonth()->toDateTimeString(),
        ]
    )
    ->where('tblinvoiceitems.type', 'Item')
    ->where('tblclients.currency', $currencyid)
    ->first(
        [
            Capsule::raw('COUNT(*) as number'),
            Capsule::raw('SUM(tblinvoiceitems.amount) as amount')
        ]
    );

$itemtotal += $data->amount;
$number = $data->number;
$amount = $data->amount;
if (!$amount) $amount="0.00";
if (!$number) $number="0";
$reportdata["tablevalues"][] = array('Billable Items',$number,formatCurrency($amount));

$data = Capsule::table('tblinvoiceitems')
    ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->whereBetween(
        'tblinvoices.datepaid',
        [
            $dateRange->startOfMonth()->toDateTimeString(),
            $dateRange->endOfMonth()->toDateTimeString(),
        ]
    )
    ->where('tblinvoiceitems.type', '')
    ->where('tblclients.currency', $currencyid)
    ->first(
        [
            Capsule::raw('COUNT(*) as number'),
            Capsule::raw('SUM(tblinvoiceitems.amount) as amount')
        ]
    );

$itemtotal += $data->amount;
$number = $data->number;
$amount = $data->amount;
$reportdata["tablevalues"][] = array('Custom Invoice Line Items',$number,formatCurrency($amount));

$total += $itemtotal;
$reportdata["tablevalues"][] = array('','<strong>Sub-Total</strong>','<strong>'.formatCurrency($itemtotal).'</strong>');
$chartdata['rows'][] = array('c'=>array(array('v'=>"Miscellaneous"),array('v'=>$itemtotal,'f'=>formatCurrency($itemtotal))));

$total = formatCurrency($total);

$chartdata['cols'][] = array('label'=>'Days Range','type'=>'string');
$chartdata['cols'][] = array('label'=>'Value','type'=>'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["footertext"] = $chart->drawChart('Pie',$chartdata,$args,'300px');

$reportdata["monthspagination"] = true;

