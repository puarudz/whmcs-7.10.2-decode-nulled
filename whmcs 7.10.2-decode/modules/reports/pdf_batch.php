<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;
use WHMCS\Invoices;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Batch PDF Invoice Export";
$reportdata["description"] = <<<DESCRIPTION
This tool can be used to generate and download a batch export of invoices in PDF format (one per page).<br />
Typical uses for this include producing hard paper copies for mailing to clients or record keeping.
DESCRIPTION;

require("../includes/gatewayfunctions.php");

if ($noresults) {
    infoBox("No Invoices Match Criteria", "No invoices were found matching the criteria you specified");
    $reportdata["description"] .= $infobox;
}

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastMonth = Carbon::today()->subDays(29)->startOfDay();
    $range = $lastMonth->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}

$clientsDropDown = $aInt->clientsDropDown($userid, false, 'userid', true);

$gatewayOptions = '';
$results = Capsule::table('tblpaymentgateways')
    ->where('setting', '=', 'name')
    ->orderBy('order', 'asc')
    ->pluck('gateway', 'value');
foreach ($results as $gateway => $value) {
    $gatewayOptions .= "<option value=\"{$value}\" selected>{$gateway}</option>";
}

$statusOptions = '';
foreach (Invoices::getInvoiceStatusValues() as $invoiceStatusOption) {
    if ($invoiceStatusOption == 'Unpaid') {
        $isSelected = 'selected';
    } else {
        $isSelected = '';
    }
    $optionName = $aInt->lang('status', strtolower(str_replace(' ', '', $invoiceStatusOption)));
    $statusOptions .= "<option value=\"{$invoiceStatusOption}\" {$isSelected}>{$optionName}</option>";
}

$reportdata["headertext"] = <<<HTML
<form method="post" action="csvdownload.php?type=pdfbatch">
    <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tr>
            <td width="20%" class="fieldlabel">
                Client Name
            </td>
            <td class="fieldarea">
                {$clientsDropDown}
            </td>
        </tr>
        <tr>
            <td class="fieldlabel">
                Filter By
            </td>
            <td class="fieldarea">
                <select name="filterby" class="form-control select-inline">
                    <option>Date Created</option>
                    <option>Due Date</option>
                    <option>Date Paid</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="fieldlabel">
                Date Range
            </td>
            <td class="fieldarea">
                <div class="form-group date-picker-prepend-icon">
                    <label for="inputFilterDate" class="field-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </label>
                    <input id="inputFilterDate"
                    type="text"
                    name="range"
                    value="{$range}"
                    class="form-control date-picker-search input-inline"
                    />
                </div>
            </td>
        </tr>
        <tr>
            <td class="fieldlabel">
                Payment Methods
            </td>
            <td class="fieldarea">
                <select name="paymentmethods[]" class="form-control input-250" size="8" multiple="true">
                    {$gatewayOptions}
                </select>
            </td>
        </tr>
        <tr>
            <td class="fieldlabel">
                Statuses
            </td>
            <td class="fieldarea">
                <select name="statuses[]" class="form-control input-150" size="6" multiple="true">
                    {$statusOptions}
                </select>
            </td>
        </tr>
        <tr>
            <td class="fieldlabel">
                Sort Order
            </td>
            <td class="fieldarea">
                <select name="sortorder" class="form-control select-inline">
                    <option>Invoice ID</option>
                    <option>Invoice Number</option>
                    <option>Date Paid</option>
                    <option>Due Date</option>
                    <option>Client ID</option>
                    <option>Client Name</option>
                </select>
            </td>
        </tr>
    </table>
    <p align=center>
        <input type="submit" value="Download File" class="btn btn-default">
    </p>
</form>
HTML;

$report = '';

