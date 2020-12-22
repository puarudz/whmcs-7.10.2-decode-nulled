<?php

use WHMCS\Database\Capsule;
use WHMCS\Utility\Country;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Clients by Country";
$reportdata["description"] = "This report shows the total number of active services per country, as well as total active unique clients per country in the table below.";

$reportdata["tableheadings"] = array("Country","Active Services","Active Clients");

$countries = new Country();
$countries = $countries->getCountryNameArray();

$clientstats = array();

$results = Capsule::table('tblclients')
    ->select(Capsule::raw('country, count(*) as `count`'))
    ->where('Status', '=', 'Active')
    ->groupBy('country')
    ->orderBy('country', 'asc')
    ->get();
foreach ($results as $result) {
    $clientstats[$result->country] = $result->count;
}

$results = Capsule::table('tblhosting')
    ->select(Capsule::raw('tblclients.country, count(*) as `count`'))
    ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
    ->where('domainstatus', '=', 'Active')
    ->groupBy('country')
    ->orderBy('country', 'asc')
    ->get();
foreach ($results as $result) {
    $countryname = $countries[$result->country];
    if ($countryname) {
        $reportdata["tablevalues"][] = [
            $countryname,
            $result->count,
            $clientstats[$result->country],
        ];

        $chartdata['rows'][] = [
            'c' => [
                ['v' => $result->country],
                ['v' => $result->count],
                ['v' => $clientstats[$result->country]],
            ]
        ];

        unset($clientstats[$result->country]);
    }
}

foreach ($clientstats AS $country=>$activeclient) {

    $countryname = $countries[$country];
    if ($countryname) {

    $reportdata["tablevalues"][] = array($countryname,'0',$activeclient);

    $chartdata['rows'][] = array('c'=>array(array('v'=>$country),array('v'=>0),array('v'=>$activeclient)));

    }

}

$chartdata['cols'][] = array('label'=>'Country','type'=>'string');
$chartdata['cols'][] = array('label'=>'Active Services','type'=>'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Geo',$chartdata,$args,'600px');
