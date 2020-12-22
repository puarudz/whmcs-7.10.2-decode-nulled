<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$months = array('January','February','March','April','May','June','July','August','September','October','November','December');

$reportdata["title"] = "Income Forecast";
$reportdata["description"] = "This report shows the projected income for each month of the year if all active services are renewed within that month";

$reportdata["currencyselections"] = true;

$reportdata["tableheadings"] = array("Month","Monthly","Quarterly","Semi-Annual","Annual","Biennial","Total");

$totals = array();

$results = Capsule::table('tblhosting')
    ->where('tblhosting.domainstatus', '=', 'Active')
    ->where('tblclients.currency', '=', (int) $currencyid)
    ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
    ->get();
foreach ($results as $result) {
    $recurringamount = $result->amount;
    $nextduedate = $result->nextduedate;
    $billingcycle = $result->billingcycle;
    $nextduedate = explode("-", $nextduedate);
    $year = $nextduedate[0];
    $month = $nextduedate[1];
    if ($billingcycle == "Monthly") {
        $recurrence = 1;
    } elseif ($billingcycle == "Quarterly") {
        $recurrence = 3;
    } elseif ($billingcycle == "Semi-Annually") {
        $recurrence = 6;
    } elseif ($billingcycle == "Annually") {
        $recurrence = 12;
    } elseif ($billingcycle == "Biennially") {
        $recurrence = 24;
    } else {
        $recurrence = 24;
    }
    $recurrences = (24 / $recurrence);
    for ($i = 0; $i <= 24; $i += $recurrence) {
        $new_time = mktime(0, 0, 0, ($month + $i), 1, $year);
        $totals[date("Y", $new_time)][date("m", $new_time)][$recurrence] += $recurringamount;
    }
}

$results = Capsule::table('tbldomains')
    ->where('tbldomains.status', '=', 'Active')
    ->where('tblclients.currency', '=', (int) $currencyid)
    ->join('tblclients', 'tblclients.id', '=', 'tbldomains.userid')
    ->get();
foreach ($results as $result) {
    $recurringamount = $result->recurringamount;
    $nextduedate = $result->nextduedate;
    $regperiod = $result->registrationperiod;
    $nextduedate = explode("-", $nextduedate);
    $year = $nextduedate[0];
    $month = $nextduedate[1];
    if (!$regperiod) {
        $regperiod = 1;
    }
    $recurrence = ($regperiod * 12);
    $recurrences = (24 / $recurrence);
    for ($i = 0; $i <= 24; $i += $recurrence) {
        $new_time = mktime(0, 0, 0, ($month + $i), 1, $year);
        $totals[date("Y", $new_time)][date("m", $new_time)][$recurrence] += $recurringamount;
    }
}

for ($i=0;$i<=24;$i++) {
    $new_time = mktime(0,0,0,date("m")+$i,1,date("Y"));
    $months_array[date("Y",$new_time)][date("m",$new_time)] = "x";
}

$overallincome = 0;

foreach ($months_array AS $year=>$month) {
    foreach ($month AS $mon=>$x) {
        $monthlyincome = $totals[$year][$mon][1]+$totals[$year][$mon][3]+$totals[$year][$mon][6]+$totals[$year][$mon][12]+$totals[$year][$mon][24];
        $overallincome += $monthlyincome;
        $chartdata['rows'][] = array('c'=>array(array('v'=>$months[$mon-1]." ".$year),array('v'=>$overallincome,'f'=>formatCurrency($overallincome))));
        $reportdata["tablevalues"][] = array($months[$mon-1]." ".$year,formatCurrency($totals[$year][$mon][1]),formatCurrency($totals[$year][$mon][3]),formatCurrency($totals[$year][$mon][6]),formatCurrency($totals[$year][$mon][12]),formatCurrency($totals[$year][$mon][24]),formatCurrency($monthlyincome));

    }
}

$reportdata["footertext"] = "<p align=\"center\"><b>Total Projected Income: ".formatCurrency($overallincome)."</b></p>";

$chartdata['cols'][] = array('label'=>'Month','type'=>'string');
$chartdata['cols'][] = array('label'=>'Cumulative Income Forecast Total','type'=>'number');

#$args['colors'] = '#80D044,#F9D88C,#CC0000';

$reportdata["headertext"] = $chart->drawChart('Area',$chartdata,$args,'450px');
