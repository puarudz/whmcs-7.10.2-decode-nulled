<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Product Suspensions";
$reportdata["description"] = "This report allows you to review all suspended products and the reasons specified for their suspensions";

$reportdata["tableheadings"] = array("Service ID","Client Name","Product Name","Domain","Next Due Date","Suspend Reason");

$results = Capsule::table('tblhosting')
    ->select('tblhosting.*', 'tblclients.firstname', 'tblclients.lastname', 'tblproducts.name')
    ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
    ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
    ->where('domainstatus', '=', 'Suspended')
    ->orderBy('id', 'asc')
    ->get();
foreach ($results as $result) {
    $serviceid = $result->id;
    $userid = $result->userid;
    $clientname = $result->firstname . " " . $result->lastname;
    $productname = $result->name;
    $domain = $result->domain;
    $nextduedate = $result->nextduedate;
    $suspendreason = $result->suspendreason;

    if (!$suspendreason) {
        $suspendreason = 'Overdue on Payment';
    }

    $nextduedate = fromMySQLDate($nextduedate);

    $reportdata["tablevalues"][] = [
        '<a href="clientshosting.php?userid=' . $userid . '&id=' . $serviceid . '">' . $serviceid . '</a>',
        '<a href="clientssummary.php?userid=' . $userid . '">' . $clientname . '</a>',
        $productname,
        $domain,
        $nextduedate,
        $suspendreason,
    ];
}

$data["footertext"] = '';
