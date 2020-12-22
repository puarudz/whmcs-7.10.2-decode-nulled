<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Service;

class ViewHelper
{
    public function serverTenantUsageTable($serviceMetrics = array())
    {
        $metricsRowTmpl = "<tr>\n   <td>%s</td>\n   <td class=\"text-center\">%s</td>\n   <td>%s</td>\n   <td class=\"text-center\">%s</td>\n</tr>";
        $textUnbilledUsage = \AdminLang::trans("usagebilling.unbilledUsage");
        $textFor = \AdminLang::trans("global.for");
        $rows = "";
        foreach ($serviceMetrics as $serviceMetric) {
            $currentValue = $serviceMetric->units()->decorate($serviceMetric->units()->roundForType($serviceMetric->usage()->value()));
            $unbilledUsageOutput = "";
            $historicalUsage = $serviceMetric->historicUsage();
            if ($historicalUsage && !valueIsZero($historicalUsage->value())) {
                $postPeriodDateRange = $historicalUsage->startAt()->toAdminDateFormat() . " - " . $historicalUsage->endAt()->toAdminDateFormat();
                $postPeriodValue = $serviceMetric->units()->decorate($serviceMetric->units()->roundForType($historicalUsage->value()));
                $unbilledUsageOutput = " <button type=\"button\" class=\"btn btn-default btn-xs\" " . "data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $postPeriodValue . " " . $textFor . " " . $postPeriodDateRange . "\"><i class=\"fa fa-info-circle\"></i> " . $textUnbilledUsage . "</button>";
            }
            $name = $serviceMetric->displayName();
            if ($serviceMetric->isEnabled()) {
                $enabled = "<i class=\"fas fa-check text-success\"></i>";
            } else {
                $enabled = "";
            }
            if ($serviceMetric->usage() instanceof \WHMCS\UsageBilling\Contracts\Metrics\UsageStubInterface) {
                $lastUpdate = $currentValue = "&mdash;";
            } else {
                $lastUpdate = $serviceMetric->usage()->collectedAt()->diffForHumans();
            }
            $rows .= sprintf($metricsRowTmpl, $name, $enabled, $currentValue . $unbilledUsageOutput, $lastUpdate);
        }
        $textMetric = \Lang::trans("metrics.metric");
        $textEnabled = \AdminLang::trans("global.enabled");
        $textCurrentUsage = \Lang::trans("metrics.currentUsage");
        $textLastUpdate = \Lang::trans("metrics.lastUpdated");
        $html = "    <table class=\"table table-striped table-condensed\" style=\"margin-bottom:5px;border-bottom:1px solid #ddd;\">\n         <tr>\n            <th width=\"25%\" class=\"text-center\">" . $textMetric . "</th>\n            <th width=\"20%\" class=\"text-center\">" . $textEnabled . "</th>\n            <th>" . $textCurrentUsage . "</th>\n            <th class=\"text-center\">" . $textLastUpdate . "</th>\n        </tr>\n        " . $rows . "\n    </table>";
        return $html;
    }
}

?>