<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Disk Space & Bandwidth Usage Summary";
$reportdata["description"] = "This report shows the Disk Space & Bandwidth Usage Statistics for hosting accounts";

$reportdata["tableheadings"] = array("Client Name/Domain","Disk Usage","Disk Limit","% Used","BW Usage","BW Limit","% Used");

if ($_GET["action"]=="updatestats") {
    require("../includes/modulefunctions.php");
    ServerUsageUpdate();
}

$results = Capsule::table('tblservers')
    ->orderBy('name', 'asc')
    ->get();
foreach ($results as $result) {
    $serverid = $result->id;
    $name = $result->name;
    $ipaddress = $result->ipaddress;
    $reportdata["tablevalues"][] = ["**<strong>{$name}</strong> - {$ipaddress}"];

    $services = Capsule::table('tblhosting')
        ->select(
            'tblhosting.domain',
            'tblhosting.diskusage',
            'tblhosting.disklimit',
            'tblhosting.bwlimit',
            'tblhosting.bwusage',
            'tblhosting.domainstatus',
            'tblclients.firstname',
            'tblclients.lastname',
            'tblclients.companyname',
            'tblhosting.lastupdate'
        )
        ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
        ->where('tblhosting.server', '=', (int) $serverid)
        ->where('tblhosting.lastupdate', '!=', '0000-00-00 00:00:00')
        ->whereIn('domainstatus', ['Active', 'Suspended'])
        ->orderBy('tblhosting.domain', 'asc')
        ->get();
    foreach ($services as $service) {
        $name = "{$service->firstname} {$service->lastname}";
        $companyname = $service->companyname;
        if ($companyname != "") {
            $name .= " ({$companyname})";
        }

        $domain = $service->domain;
        $diskusage = $service->diskusage;
        $disklimit = $service->disklimit;
        $bwusage = $service->bwusage;
        $bwlimit = $service->bwlimit;
        $lastupdate = $service->lastupdate;

        if ($disklimit == "0") {
            $percentused = "N/A";
        } else {
            @$percentused = number_format((($diskusage / $disklimit) * 100), 0, '.', '');
        }
        if ($disklimit=="0") {
            $disklimit="Unlimited";
        }
        if ($bwlimit == "0") {
            $bwpercentused = "N/A";
        } else {
            @$bwpercentused = number_format((($bwusage / $bwlimit) * 100), 0, '.', '');
        }
        if ($bwlimit == "0") {
            $bwlimit = "Unlimited";
        }
        if ($percentused != "N/A") {
            $percentused .= "%";
        }
        if ($bwpercentused != "N/A") {
            $bwpercentused .= "%";
        }

        $reportdata["tablevalues"][] = [
            "{$name}<br />{$domain}",
            "{$diskusage} MB",
            "{$disklimit} MB",
            "{$percentused}",
            "{$bwusage} MB",
            "{$bwlimit} MB",
            "{$bwpercentused}"
        ];
    }
}

$data["footertext"] = "<p>Disk Space Usage Stats Last Updated at "
    . fromMySQLDate($lastupdate, "time")
    . " - <a href=\"{$_SERVER["PHP_SELF"]}?report={$_GET["report"]}&action=updatestats\">Update Now</a></p>";
