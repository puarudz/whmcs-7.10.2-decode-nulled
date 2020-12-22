<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Setup\Product;

class MetricPriceViewHelper
{
    public function getMetricPricingModalBody(\WHMCS\UsageBilling\Product\UsageItem $usageItem)
    {
        $productId = $usageItem->rel_id;
        $metric = $usageItem->getModuleMetric();
        $units = $metric->units();
        $unit = $units->name();
        $currencies = \WHMCS\Billing\Currency::defaultSorting()->get();
        $postUrl = \WHMCS\Utility\Environment\WebHelper::getAdminBaseUrl() . "/configproducts.php";
        $brackets = $usageItem->pricingSchema;
        $schemaTypes = $brackets->getSchemaTypes();
        $schemaTypeSelected = $brackets->schemaType();
        $isSimpleModeClass = "";
        if ($schemaTypeSelected === \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_SIMPLE) {
            $isSimpleModeClass = " hidden";
        }
        if (!$unit) {
            $unit = \AdminLang::trans("usagebilling.unit.wholeNumber");
        }
        $inclusiveBracket = null;
        $bracketRows = "";
        $removeBtn = "<button class=\"btn btn-default btn-remove\">\n                        <i class=\"fa fa-times\"></i>\n                    </button>";
        if ($metric->type() == \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_PERIOD_DAY) {
            $metricType = \AdminLang::trans("usagebilling.metricType.day");
        } else {
            if ($metric->type() == \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_PERIOD_MONTH) {
                $metricType = \AdminLang::trans("usagebilling.metricType.month");
            } else {
                $metricType = \AdminLang::trans("usagebilling.metricType.snapshot");
            }
        }
        $minimumCycle = "monthly";
        $emptyBrackets = "";
        $emptyCurrencyCells = "";
        foreach ($currencies as $currency) {
            $emptyCurrencyCells .= $this->currencyCell($currency);
        }
        if ($brackets->count() == 0) {
            $emptyBrackets = "            <tr>\n                <td class=\"non-simple-pricing hidden\">\n                    <input type=\"text\" class=\"form-control input-above\"\n                        name=\"above[]\" value=\"0\" readonly>\n                </td>\n                " . $emptyCurrencyCells . "\n                <td class=\"non-simple-pricing hidden\"></td>\n            </tr>";
        } else {
            $i = 0;
            foreach ($brackets as $bracket) {
                $btn = "";
                if (0 < $i) {
                    $btn = $removeBtn;
                }
                $currencyCells = "";
                $pricing = $bracket->pricing;
                foreach ($currencies as $currency) {
                    $savedCurrencyPricing = null;
                    foreach ($pricing as $price) {
                        if ($price->currencyId == $currency->id) {
                            $savedCurrencyPricing = $this->currencyCell($price->currency, $price->{$minimumCycle});
                        }
                    }
                    if ($savedCurrencyPricing) {
                        $currencyCells .= $savedCurrencyPricing;
                    } else {
                        $currencyCells .= $this->currencyCell($currency);
                    }
                }
                $floor = 0;
                $readonly = "";
                if (!valueIsZero($bracket->floor)) {
                    $floor = $units->formatForType($bracket->floor);
                } else {
                    $readonly = " readonly";
                }
                if ($i < 1) {
                    $nonSimpleClassRow = "";
                    $nonSimpleClassCell = "non-simple-pricing";
                    if ($isSimpleModeClass) {
                        $nonSimpleClassCell .= $isSimpleModeClass;
                    }
                } else {
                    $nonSimpleClassCell = "";
                    $nonSimpleClassRow = "non-simple-pricing";
                }
                $bracketRows .= "            <tr class=\"" . $nonSimpleClassRow . "\">\n                <td class=\"" . $nonSimpleClassCell . "\">\n                    <input type=\"text\"\n                        class=\"form-control input-above\"\n                        name=\"above[]\"\n                        value=\"" . $floor . "\" " . $readonly . ">\n                </td>\n                " . $currencyCells . "\n                <td class=\"" . $nonSimpleClassCell . "\">\n                    " . $btn . "\n                </td>\n            </tr>";
                $i++;
            }
        }
        $startSimple = $isSimpleModeClass ? 1 : 0;
        $js = $this->tableJs($startSimple);
        $token = generate_token("form");
        $currencyHeader = "";
        foreach ($currencies as $currency) {
            $currencyHeader .= "<td class=\"heading\">" . $currency->code . "</td>";
        }
        $typeTitle = \AdminLang::trans("usagebilling.schematypetitle");
        $typeOptions = array();
        foreach ($schemaTypes as $type) {
            $typeOptions[] = "<label class=\"radio-inline\">" . "<input class=\"schemaType\" type=\"radio\" name=\"schemaType\" value=\"" . $type . "\"" . ($type == $schemaTypeSelected ? " checked=\"checked\"" : "") . "> " . \AdminLang::trans("usagebilling.schematype." . $type) . "</label> " . "<i class=\"fal fa-info-square\" data-toggle=\"tooltip\" data-placement=\"bottom\" " . "title=\"" . addslashes(\Lang::trans("metrics.pricingschema." . $type . ".detail")) . "\">" . "</i>";
        }
        $pricingMatrixClass = "";
        $included = $units->formatForType($usageItem->included);
        $typeOptions = implode(" &nbsp; ", $typeOptions);
        $setupUnits = \AdminLang::trans("usagebilling.unit.title");
        $textMetricType = \AdminLang::trans("usagebilling.metricType.title");
        $pricingTitle = \AdminLang::trans("usagebilling.pricingtitle");
        $textStartingNumber = \AdminLang::trans("usagebilling.starting") . "<br/>" . \AdminLang::trans("usagebilling.quantity");
        $textAddrow = \AdminLang::trans("usagebilling.addrow");
        $textRangeInfo = \AdminLang::trans("usagebilling.startnumberinfo");
        $textIncludedTitle = \AdminLang::trans("usagebilling.quantityIncluded");
        $textPricePerUnit = \AdminLang::trans("usagebilling.pricePerUnit", array(":unit" => $units->perUnitName(1)));
        $html = "<form method=\"post\" action=\"" . $postUrl . "\">\n    " . $token . "\n    <input type=\"hidden\" name=\"module\"\n        value=\"" . $usageItem->module->getLoadedModule() . "\" />\n    <input type=\"hidden\" name=\"id\" value=\"" . $productId . "\" />\n    <input type=\"hidden\" name=\"metric\" value=\"" . $metric->systemName() . "\" />\n    <input type=\"hidden\" name=\"action\" value=\"save-metric-pricing\" />\n    <h2>" . $metric->displayName() . "</h2>\n    <p>" . $textMetricType . ": " . $metricType . "</p>\n            <p>" . $setupUnits . ": " . $unit . "</p>\n    \n    <div class=\"row\">\n        <div class=\"col-sm-8\">\n            <p><strong>" . $pricingTitle . "</strong></p>\n            <p>" . $typeTitle . ":<br/>" . $typeOptions . "</p>\n        </div>\n        <div class=\"col-sm-4\" style=\"text-align: right\">\n            <label for=\"included\">" . $textIncludedTitle . "</label>\n            <input class=\"form-control input-inline input-125\" type=\"text\" name=\"included\" value=\"" . $included . "\" />\n        </div>\n    </div>\n    <div id=\"containerPricingMatrix\" class=\"" . $pricingMatrixClass . "\">\n        <table class=\"table table-striped metric-pricing-matrix\">\n            <tr>\n                <td class=\"heading non-simple-pricing " . $isSimpleModeClass . "\" width=\"140px\" rowspan=\"2\">\n                    " . $textStartingNumber . "<br/>\n                    <i class=\"fal fa-info-square\" data-toggle=\"tooltip\" data-placement=\"bottom\"\n                    title=\"" . $textRangeInfo . "\">\n                    </i>\n                </td>\n                <td class=\"heading\" colspan=\"" . $currencies->count() . "\">" . $textPricePerUnit . "</td>\n                <td class=\"heading non-simple-pricing " . $isSimpleModeClass . "\" width=\"40\" rowspan=\"2\">\n                </td>\n            </tr>\n            <tr>\n            " . $currencyHeader . "\n            </tr>\n            <tr class=\"new-row hidden\">\n                <td class=\"\">\n                    <input type=\"text\" name=\"above[]\"\n                        class=\"form-control input-above\" value=\"\">\n                </td>\n                " . $emptyCurrencyCells . "\n                <td class=\"\">" . $removeBtn . "</td>\n            </tr>\n            " . $bracketRows . "\n            " . $emptyBrackets . "\n        </table>\n        <button type=\"button\" href=\"#\" class=\"btn btn-default btn-sm metric-pricing-matrix-add-row non-simple-pricing " . $isSimpleModeClass . "\">" . $textAddrow . "</button>\n    </div>\n</form>\n<style>\n    .heading { font-weight: bold; background-color: #efefef; text-align: center; padding: 3px 5px; }\n</style>\n\n<script>\n    " . $js . "\n</script>";
        return $html;
    }
    protected function currencyCell(\WHMCS\Billing\Currency $currency, $price = NULL, $name = "pricing")
    {
        if (is_null($price)) {
            $price = "0.00";
        }
        return sprintf("<td><input type=\"text\" name=\"%s[%s][]\" " . "class=\"form-control pricing\" value=\"%s\"></td>", $name, $currency->id, $price);
    }
    protected function tableJs($isSimpleMode)
    {
        return "\$(document).ready(function() {\n        jQuery('[data-toggle=\"tooltip\"]').tooltip();\n        // keep ENTER in range inputs from triggering events\n        jQuery('body').on('keydown', '.input-above', function (e) {\n            if (e.keyCode === 13) {\n                e.preventDefault();\n            }\n        });\n\n        var isSimpleMode = " . $isSimpleMode . ";\n        \$('input.schemaType').click(function(e){\n            var input = \$(this);\n            if (input.val() === 'simple') {\n                isSimpleMode = true;\n                \$('#containerPricingMatrix').find('.non-simple-pricing').hide();\n            } else if (isSimpleMode) {\n                \$('#containerPricingMatrix').find('.non-simple-pricing')\n                    .removeClass('hidden').show();\n            }\n        });\n        \n        \$(\".metric-pricing-matrix-add-row\").click(function(e) {\n            e.preventDefault();\n\n            var maxValue = 0;\n            \$(\".metric-pricing-matrix .input-above\").each(function(i) {\n                if (\$.isNumeric(\$(this).val())) {\n                    if (\$(this).val() > maxValue) {\n                        maxValue = \$(this).val();\n                    }\n                }\n            });\n            if (maxValue > 0) {\n                var val = maxValue.split('.');\n                if (\$.isNumeric(val[1])) {\n                    var int = parseInt(val[1]);\n                    var places = String(val[1]).length - String(int).length;\n                    if (places === 0) {\n                        int += 1;\n                        if (String(val[1]).length < String(int).length) {\n                            maxValue = parseInt(maxValue) + 1;\n                        } else {\n                            maxValue = parseFloat(val[0] + '.' + int);\n                        }\n                    } else if (places > 0) {\n                        int += 1;\n                        places = String(val[1]).length - String(int).length;\n                        if (places === 0) {\n                            maxValue = parseFloat(val[0] + '.' + int);\n                        } else if (places > 0) {\n                            var zero = '0';\n                            int = String(zero.repeat(places)) + String(int);\n                            maxValue = parseFloat(val[0] + '.' + int);\n                        } else {\n                            maxValue = parseInt(maxValue) + 1;\n                        }\n                    }\n                } else {\n                    maxValue = parseInt(maxValue) + 1;\n                }\n            }\n            var newrow = \$(\".metric-pricing-matrix tr.new-row\");\n            newrow.find('.non-simple-pricing-placeholder')\n            .removeClass('non-simple-pricing-placeholder')\n            .addClass('.non-simple-pricing');\n            \n            \$(\".metric-pricing-matrix\")\n                .append(\n                    '<tr class=\"non-simple-pricing\" >' \n                    + newrow.html() \n                    + \"</tr>\"\n                );\n            \$(\".metric-pricing-matrix tr:last-child .input-above\").val(maxValue);\n        });\n        jQuery(\"body\").on(\"click\", \".metric-pricing-matrix button.btn-remove\", function(e) {\n            e.preventDefault();\n            \$(this).closest(\"tr:visible\").remove();\n        });\n    });";
    }
}

?>