<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<form id=\"frmTldImport\" class=\"form-horizontal\" method=\"post\">\n    <div class=\"admin-tabs-v2\">\n        <div class=\"form-group\">\n            <label for=\"inputMarginType\" class=\"col-md-4 col-sm-6 control-label\">\n                ";
echo AdminLang::trans("domains.tldImport.marginType");
echo "                <br>\n                <small>\n                    ";
echo AdminLang::trans("domains.tldImport.fixedOrPercentage");
echo "                </small>\n            </label>\n            <div class=\"col-md-4 col-sm-6\">\n                <select id=\"inputMarginType\" class=\"form-control select-inline\">\n                    <option value=\"percentage\" selected=\"selected\">\n                        ";
echo AdminLang::trans("global.percentage");
echo "                    </option>\n                    <option value=\"fixed\">\n                        ";
echo AdminLang::trans("global.fixedAmount");
echo "                    </option>\n                </select>\n            </div>\n        </div>\n        <div class=\"form-group\">\n            <label for=\"inputMarginPercent\" class=\"col-md-4 col-sm-6 control-label\">\n                ";
echo AdminLang::trans("domains.tldImport.profitMargin");
echo "                <br>\n                <small>\n                    ";
echo AdminLang::trans("domains.tldImport.profitMarginDescription");
echo "                </small>\n            </label>\n            <div class=\"col-md-4 col-sm-6\">\n                <div class=\"tld-import-percentage-margin\">\n                    <div class=\"input-group\">\n                        <input id=\"inputMarginPercent\"\n                               type=\"number\"\n                               class=\"form-control\"\n                               value=\"20\"\n                        >\n                        <span class=\"input-group-addon hidden-sm\">%</span>\n                    </div>\n                </div>\n                <div class=\"tld-import-fixed-margin hidden\">\n                    <div class=\"input-group\">\n                        <span class=\"input-group-addon hidden-sm\">";
echo $currency["prefix"];
echo "</span>\n                        <input id=\"inputMarginFixed\"\n                               type=\"number\"\n                               class=\"form-control input-75\"\n                               value=\"20.00\"\n                               step=\"0.01\"\n                        >\n                        <span class=\"input-group-addon hidden-sm\">";
echo $currency["suffix"];
echo "</span>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"form-group\">\n            <label for=\"inputRoundingValue\" class=\"col-md-4 col-sm-6 control-label\">\n                ";
echo AdminLang::trans("domains.tldImport.rounding");
echo "                <br>\n                <small>\n                    ";
echo AdminLang::trans("domains.tldImport.roundingDescription");
echo "                </small>\n            </label>\n            <div class=\"col-md-4 col-sm-6\">\n                <select id=\"inputRoundingValue\"\n                        class=\"form-control select-inline\"\n                >\n                    <option value=\"\" selected=\"selected\">\n                        ";
echo AdminLang::trans("domains.tldImport.noRounding");
echo "                    </option>\n                    <option value=\"1.00\">x.00</option>\n                    <option value=\"0.09\">x.09</option>\n                    <option value=\"0.19\">x.19</option>\n                    <option value=\"0.29\">x.29</option>\n                    <option value=\"0.39\">x.39</option>\n                    <option value=\"0.49\">x.49</option>\n                    <option value=\"0.50\">x.50</option>\n                    <option value=\"0.59\">x.59</option>\n                    <option value=\"0.69\">x.69</option>\n                    <option value=\"0.79\">x.79</option>\n                    <option value=\"0.89\">x.89</option>\n                    <option value=\"0.95\">x.95</option>\n                    <option value=\"0.99\">x.99</option>\n                </select>\n            </div>\n        </div>\n        <div class=\"form-group\">\n            <label for=\"inputSyncRedemption\" class=\"col-md-4 col-sm-6 control-label\">\n                ";
echo AdminLang::trans("domains.tldImport.syncRedemption");
echo "<br>\n                <small>\n                    ";
echo AdminLang::trans("domains.tldImport.syncRedemptionDescription");
echo "                </small>\n            </label>\n            <div class=\"col-md-4 col-sm-6\">\n                <input id=\"inputSyncRedemption\"\n                       type=\"checkbox\"\n                       name=\"sync_redemption\"\n                       value=\"1\"\n                       data-on-text=\"";
echo AdminLang::trans("global.yes");
echo "\"\n                       data-off-text=\"";
echo AdminLang::trans("global.no");
echo "\"\n                >\n            </div>\n        </div>\n        <div class=\"form-group\">\n            <label for=\"inputSetAutoRegistrar\" class=\"col-md-4 col-sm-6 control-label\">\n                ";
echo AdminLang::trans("domains.tldImport.setAutoRegistrar");
echo "<br>\n                <small>\n                    ";
echo AdminLang::trans("domains.tldImport.setAutoRegistrarDescription");
echo "                </small>\n            </label>\n            <div class=\"col-md-4 col-sm-6\">\n                <input id=\"inputSetAutoRegistrar\"\n                       type=\"checkbox\"\n                       name=\"set_auto_register\"\n                       value=\"1\"\n                       data-on-text=\"";
echo AdminLang::trans("global.yes");
echo "\"\n                       data-off-text=\"";
echo AdminLang::trans("global.no");
echo "\"\n                >\n            </div>\n        </div>\n        <div class=\"form-group\">\n            <div class=\"col-sm-8\">\n                <button id=\"btnSelectRegistrar\" type=\"button\" class=\"btn btn-primary\">\n                    ";
echo AdminLang::trans("domains.tldImport.selectRegistrar");
echo "                </button>\n            </div>\n            <div class=\"col-sm-4 text-right\">\n                <button id=\"doTldImport\" type=\"button\" class=\"btn btn-primary\">\n                    ";
echo AdminLang::trans("domains.tldImport.importCountTlds", array(":count" => "<span id=\"tldImportCount\">0</span>"));
echo "                </button>\n            </div>\n        </div>\n    </div>\n    <div class=\"admin-tabs-v2\">\n        <ul class=\"nav nav-tabs admin-tabs admin-tabs-v2 tld-tabs\" role=\"tablist\">\n            ";
$i = 1;
echo "            ";
foreach ($categories as $category => $data) {
    if (!count($data)) {
        continue;
    }
    echo "\n                <li class=\"";
    echo $i === 1 ? "active" : "";
    echo "\" role=\"presentation\">\n                    <a href=\"#tab";
    echo str_replace(" ", "", ucwords($category));
    echo "\"\n                       role=\"tab\"\n                       data-toggle=\"tab\"\n                       id=\"tabLink";
    echo $i;
    echo "\"\n                       data-tab-id=\"";
    echo $i;
    echo "\"\n                    >\n                        ";
    echo $category;
    echo "                    </a>\n                </li>\n                ";
    $i++;
}
echo "        </ul>\n        <div class=\"tab-content admin-tabs\">\n            ";
$i = 1;
foreach ($categories as $category => $data) {
    if (!count($data)) {
        continue;
    }
    $cat = str_replace(" ", "", ucwords($category));
    echo "                <div class=\"tab-pane";
    echo $i === 1 ? " active" : "";
    echo "\"\n                     id=\"tab";
    echo $cat;
    echo "\"\n                     data-category=\"";
    echo $cat;
    echo "\"\n                >\n                    <div class=\"alert alert-warning text-center\" role=\"alert\" style=\"padding: 4px 15px;\">\n                        ";
    if (0 < count($tldCurrencies)) {
        echo AdminLang::trans("domains.tldImport.additionalCurrencies", array(":currencies" => implode(", ", $tldCurrencies)));
    }
    echo "                        ";
    echo AdminLang::trans("domains.tldImport.defaultCurrency", array(":currency" => $currency["code"]));
    echo "                    </div>\n                    <table class=\"table table-striped table-hover\">\n                        <thead>\n                        <tr>\n                            <th rowspan=\"2\" class=\"tld-check-all-th\">\n                                <label>\n                                    <input type=\"checkbox\" data-category=\"";
    echo $cat;
    echo "\" class=\"check-all-tlds\">\n                                </label>\n                            </th>\n                            <td colspan=\"4\" class=\"text-center\">\n                                &nbsp;\n                            </td>\n                            <th class=\"text-center\">\n                                ";
    echo AdminLang::trans("global.register");
    echo "                            </th>\n                            <th class=\"text-center\">\n                                ";
    echo AdminLang::trans("global.renew");
    echo "                            </th>\n                            <th class=\"text-center\">\n                                ";
    echo AdminLang::trans("global.transfer");
    echo "                            </th>\n                            ";
    if ($showGrace) {
        echo "                                <th class=\"text-center\">\n                                    ";
        echo AdminLang::trans("global.grace");
        echo "                                </th>\n                            ";
    }
    echo "                            <th class=\"text-center\">\n                                ";
    echo AdminLang::trans("global.redemption");
    echo "                            </th>\n                            <th rowspan=\"2\"></th>\n                        </tr>\n                        <tr>\n                            <th style=\"width: 150px;\">";
    echo AdminLang::trans("fields.tld");
    echo "</th>\n                            <th class=\"tld-import-list text-center\"> </th>\n                            <th class=\"tld-import-list text-center\">\n                                ";
    echo AdminLang::trans("domains.tldImport.existingTld");
    echo "                            </th>\n                            <th class=\"tld-import-list text-center\">\n                                ";
    echo AdminLang::trans("fields.regperiod");
    echo "                            </th>\n                            ";
    $maximumIValue = $showGrace ? 5 : 4;
    for ($i = 1; $i <= $maximumIValue; $i++) {
        echo "                            <td class=\"text-center tld-pricing-td\">\n                                <span class=\"inline-block tld-pricing\">\n                                    <span class=\"local-pricing\">\n                                        ";
        echo AdminLang::trans("domains.tldImport.local");
        echo "                                    </span>\n                                    <br>\n                                    <span class=\"remote-pricing\">\n                                        ";
        echo AdminLang::trans("domains.tldImport.cost");
        echo "                                    </span>\n                                </span>\n                                <span class=\"tld-margin\">\n                                    ";
        echo AdminLang::trans("domains.tldImport.margin");
        echo "                                </span>\n                            </td>\n                            ";
    }
    echo "                        </tr>\n                        </thead>\n                        <tbody>\n                        ";
    foreach ($data as $tld) {
        $extension = $tld->getExtension();
        $isExistingTld = $existingTldMap->has($extension);
        echo "                            <tr data-tld=\"";
        echo $extension;
        echo "\" class=\"";
        echo !$currencies->has($tld->getCurrency()) ? "no-currency" : "";
        echo "\">\n                                <td>\n                                    ";
        if ($currencies->has($tld->getCurrency())) {
            echo "                                        <input type=\"checkbox\"\n                                               name=\"tld[]\"\n                                               value=\"";
            echo $extension;
            echo "\"\n                                               data-tld=\"";
            echo $extension;
            echo "\"\n                                               data-auto-registrar=\"";
            echo $isExistingTld ? $registrarMap[$tld->getExtension()] : "";
            echo "\"\n                                               class=\"toggle-switch tld-checkbox\"\n                                               id = \"tld";
            echo $cat . $extension;
            echo "\"\n                                            ";
            echo $checked;
            echo "                                        >\n                                    ";
        }
        echo "                                </td>\n                                <td>\n                                    <label for=\"tld";
        echo $cat . $extension;
        echo "\">\n                                        ";
        echo $extension;
        echo "                                    </label>\n                                </td>\n                                <td class=\"text-center\">\n                                    ";
        if ($isExistingTld) {
            $autoRegistrar = $registrarMap[$extension];
            if ($autoRegistrar === $registrar) {
                $autoRegClass = "fa-cog text-success";
                $text = "domains.tldImport.autoRegisterWithRegistrar";
            } else {
                if (!$autoRegistrar) {
                    $autoRegClass = "fa-cog textgrey";
                    $text = "domains.tldImport.autoRegisterNotEnabled";
                } else {
                    $autoRegClass = "fa-exclamation-triangle text-warning";
                    $text = "domains.tldImport.autoRegisterOtherRegistrar";
                }
            }
            echo "                                        <i class=\"fas ";
            echo $autoRegClass;
            echo "\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"";
            echo AdminLang::trans($text, array(":registrar" => $autoRegistrar));
            echo "\"></i>\n                                    ";
        }
        echo "                                </td>\n                                <td class=\"text-center\">\n                                    <i class=\"fas fa-";
        echo $isExistingTld ? "check text-success" : "times";
        echo "\"></i>\n                                </td>\n                                <td class=\"text-center\">\n                                    ";
        echo min($tld->getYears()) . " " . AdminLang::trans("domains.year" . (1 < min($tld->getYears()) ? "s" : ""));
        echo "                                </td>\n                                ";
        if (!$currencies->has($tld->getCurrency())) {
            echo "                                    <td class=\"text-center tld-pricing-td no-currency negative\" colspan=\"4\">\n                                        ";
            if (is_null($tld->getCurrency())) {
                echo "                                            ";
                echo AdminLang::trans("domains.tldImport.noCurrencyDefined");
                echo "                                        ";
            } else {
                echo "                                            ";
                echo AdminLang::trans("domains.tldImport.noCurrency", array(":currencyCode" => $tld->getCurrency() ?: "Unknown"));
                echo "                                        ";
            }
            echo "                                    </td>\n                                ";
        } else {
            echo "                                    ";
            foreach (array("register", "renew", "transfer", "grace", "redemption") as $type) {
                if (!$showGrace && $type == "grace") {
                    continue;
                }
                $tdClass = " " . $type . "-pricing";
                $margins = array();
                if ($isExistingTld) {
                    $margins = $pricing[$extension][$type]["margin"];
                    if ($margins["percentage"] < 0) {
                        $tdClass .= " negative";
                    }
                }
                echo "                                        <td class=\"text-center tld-pricing-td";
                echo $tdClass;
                echo "\">\n                                            <span class=\"tld-pricing inline-block\">\n                                                <span class=\"local-pricing\">\n                                                    ";
                echo $pricing[$extension][$type]["selling"] ?: "-";
                echo "                                                </span><br>\n                                                <span class=\"remote-pricing\">\n                                                    ";
                echo $pricing[$extension][$type]["cost"];
                echo "                                                </span>\n                                            </span>\n                                            <span class=\"tld-margin\">\n                                                <span>\n                                                    ";
                if ($isExistingTld) {
                    foreach ($margins as $type => $margin) {
                        $suffix = $type == "percentage" ? "%" : "";
                        echo "                                                            <span class=\"inline-block ";
                        echo $type;
                        echo "-display\">\n                                                                ";
                        echo $margin . $suffix;
                        echo "                                                            </span>\n                                                        ";
                    }
                    echo "                                                    ";
                } else {
                    echo "                                                        -\n                                                    ";
                }
                echo "                                                </span>\n                                            </span>\n                                        </td>\n                                    ";
            }
            echo "                                ";
        }
        echo "                                <td class=\"text-center pricing-button\">\n                                    ";
        if ($isExistingTld) {
            echo "                                        <button type=\"button\"\n                                                class=\"btn btn-default btn-sm\"\n                                                onclick=\"openPricingPopup(";
            echo $existingTldMap[$extension];
            echo ");return false;\">\n                                            ";
            echo AdminLang::trans("global.pricing");
            echo "                                        </button>\n                                    ";
        }
        echo "                                </td>\n                            </tr>\n                        ";
    }
    echo "                        </tbody>\n                    </table>\n                </div>\n                ";
    $i++;
}
echo "        </div>\n        <div class=\"pull-right top-margin-5\">\n            <button id=\"doTldImport2\" type=\"button\" class=\"btn btn-primary\">\n                ";
echo AdminLang::trans("domains.tldImport.importCountTlds", array(":count" => "<span id=\"tldImportCount2\">0</span>"));
echo "            </button>\n        </div>\n    </div>\n</form>\n";

?>