<?php
/**
 * VAT MOSS Settlement Data Report
 *
 * This report is designed to provide the information necessary to be
 * able to complete a VAT MOSS return.
 *
 * @package    WHMCS
 * @author     WHMCS Limited <development@whmcs.com>
 * @copyright  Copyright (c) WHMCS Limited 2005-2019
 * @license    https://www.whmcs.com/license/ WHMCS Eula
 * @version    $Id$
 * @link       https://www.whmcs.com/
 */

use WHMCS\Billing\Tax\Vat;
use WHMCS\Database\Capsule;
use WHMCS\Utility\Country;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Define report parameters.
$reportdata['title'] = "VAT MOSS Settlement Data";
$reportdata['description'] = "This report provides the information needed to complete a VATMOSS return. "
    . "Please check with your tax authority to confirm how you can upload your settlement data into the MOSS portal. "
    . "You should also contact your MOSS registration country if you have any further questions in relation to the MOSS return.";

// Fetch input parameters.
$reportQuarter = $whmcs->get_req_var('reportquarter');

// List of EU Countries.
$euCountries = array_keys(Vat::EU_COUNTRIES);

// Quarter definitions.
$periodLabels = array(
    1 => 'January - March',
    2 => 'April - June',
    3 => 'July - September',
    4 => 'October - December',
);

// Initialise variables.
$queryStartDate = '';
$queryEndDate = '';
$selectHtml = '';
$currencyCode = (isset($currency['code'])) ? $currency['code'] : '';

// Build dropdown of quarters.
$periods = array();
for ($i = 2015; $i <= date("Y"); $i++) {
    $quartersToShow = ($i < date("Y")) ? 4 : ceil(date("m") / 3);
    for ($a = 1; $a <= $quartersToShow; $a++) {
        $periodLabel = $i . ' Q' . $a . ' - ' . $periodLabels[$a];
        $selectHtml .= '<option'
            . ($periodLabel == $reportQuarter ? ' selected' : '')
            . '>' . $periodLabel . '</option>';
        if ($periodLabel == $reportQuarter) {
            $queryStartDate = mktime(0, 0, 0, (($a-1)*3)+1, 1, $i);
            $queryEndDate = mktime(0, 0, 0, ($a*3)+1, 0, $i);
        }
    }
}

// Form to select quarter.
$reportdata['description'] .= '<br /><br /><form method="post" action="?report=' . $report . '">
    <div align="center">
        Select Quarter:
        <select name="reportquarter" class="form-control select-inline">
            ' . $selectHtml . '
        </select>
        <input type="submit" value="Generate Report" class="btn btn-primary" />
    </div>
</form>
';

if (!$reportQuarter) {
    $reportdata['headertext'] .= '<p align="center">Currency selection will become available on report generation.</p>';
}
// Generate report if period is selected.
if ($queryStartDate && $queryEndDate) {

    $reportdata['currencyselections'] = true;

    // Define table headings.
    $reportdata['tableheadings'] = array(
        'Country Name',
        'Country Code',
        'VAT Rate',
        'Number of Invoices',
        'Total Value Invoiced (Excl. VAT)',
        'Total VAT Collected',
        'Currency',
    );

    // Output reporting period.
    $reportdata['headertext'] .= '<h2 style="margin:0;">For Period '
        . date("jS F Y", $queryStartDate)
        . ' to '
        . date("jS F Y", $queryEndDate)
        . '</h2>';

    // Fetch country names.
    $countries = new Country();
    $countries = $countries->getCountryNameArray();

    // Fetch all configured country based tax rates.
    $taxRates = Capsule::table('tbltax')
        ->where('state', '')
        ->where('country', '!=', '')
        ->pluck('taxrate', 'country');

    // Build query to calculate data for report.
    $results = Capsule::table('tblinvoices')
        ->select(
            'tblclients.country',
            'tblinvoicedata.country as invoice_country',
            Capsule::raw('count(tblinvoices.id) as `invoicecount`'),
            Capsule::raw('sum(tblinvoices.subtotal) as `totalinvoiced`'),
            Capsule::raw('sum(tblinvoices.tax + tblinvoices.tax2) as `totalvat`')
        )
        ->distinct()
        ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
        ->leftJoin('tblinvoicedata', 'tblinvoices.id', '=', 'tblinvoicedata.invoice_id')
        ->leftJoin('tblinvoiceitems', function ($join) {
            $join->on('tblinvoiceitems.invoiceid', '=', 'tblinvoices.id');
            $join->on(function ($join) {
                $join
                    ->on('tblinvoiceitems.type', '=', Capsule::raw('"Add Funds"'))
                    ->orOn('tblinvoiceitems.type', '=', Capsule::raw('"Invoice"'));
            });
        })
        ->where(function ($query) {
            $query->where('tblinvoices.tax', '>', '0')
                ->orWhere('tblinvoices.tax2', '>', '0');
        })
        ->where(function ($query) use ($euCountries) {
            $query->where(function ($query) use ($euCountries) {
                $query->whereNotNull('tblinvoicedata.country')
                    ->whereIn('tblinvoicedata.country', $euCountries);
            })
                ->orWhere(function ($query) use ($euCountries) {
                    $query->whereNull('tblinvoicedata.country')
                        ->whereIn('tblclients.country', $euCountries);
                });
        })
        ->whereBetween('datepaid', [
            date("Y-m-d", $queryStartDate),
            date("Y-m-d", $queryEndDate) . ' 23:59:59',
        ])
        ->where('tblinvoices.status', '=', 'Paid')
        ->where('currency', '=', (int) $currencyid)
        ->whereNull('tblinvoiceitems.id')
        ->groupBy('tblclients.country')
        ->orderBy('tblclients.country', 'asc')
        ->get();

    foreach ($results as $result) {
        $countryCode = $result->country;
        if ($result->invoice_country) {
            $countryCode = $result->invoice_country;
        }
        $invoiceCount = $result->invoicecount;
        $totalInvoiced = $result->totalinvoiced;
        $totalVat = $result->totalvat;

        if (isset($countries[$countryCode])) {
            $countryName = $countries[$countryCode];
        } else {
            $countryName = 'Unrecognised Country';
        }
        if (isset($taxRates[$countryCode])) {
            $taxRate = $taxRates[$countryCode] . '%';
        } else {
            $taxRate = 'Tax Rate Not Found';
        }

        $reportdata['tablevalues'][] = [
            $countryName,
            $countryCode,
            $taxRate,
            $invoiceCount,
            $totalInvoiced,
            $totalVat,
            $currencyCode,
        ];
    }

    $reportdata['footertext'] = "* If a country does not appear in the report, then no VAT was collected "
        . "from customers in that country during the period selected.";
    $reportdata['footertext'] .= "<br />Isle of Man (GB) and Monaco (FR) are listed in this report as "
        . "EU Overseas Territories of their respective countries and should be included in any figures "
        . "provided to tax authorities. "
        . "<a href='http://europa.eu/youreurope/business/vat-customs/cross-border/index_en.htm' "
        . "target='_blank'>More Information</a>";

}
