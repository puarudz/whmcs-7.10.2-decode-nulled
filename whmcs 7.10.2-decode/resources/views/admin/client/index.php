<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<form id=\"frmClientSearch\" method=\"post\" action=\"clients.php\">\n    <input id=\"status\" type=\"hidden\" name=\"status\" value=\"";
echo $searchCriteria["status"];
echo "\" />\n    <div class=\"search-bar\" id=\"search-bar\">\n        <div class=\"simple\">\n            <div class=\"search-icon\">\n                <div class=\"icon-wrapper\">\n                    <i class=\"fas fa-search\"></i>\n                </div>\n            </div>\n            <div class=\"search-fields\">\n                <div class=\"row\">\n                    <div class=\"col-xs-12 col-sm-4 col-md-3 col-lg-2\">\n                        <div class=\"form-group\">\n                            <label for=\"inputName\">\n                                ";
echo AdminLang::trans("searchOptions.clientCompanyName");
echo "                            </label>\n                            <input type=\"text\" name=\"name\" id=\"inputName\" class=\"form-control\"\n                                value=\"";
echo e($searchCriteria["name"]);
echo "\">\n                        </div>\n                    </div>\n                    <div class=\"col-sm-4 col-md-3 col-lg-2 hidden-xs\">\n                        <div class=\"form-group\">\n                            <label for=\"inputEmail\">\n                                ";
echo AdminLang::trans("fields.email");
echo "                            </label>\n                            <input type=\"text\" name=\"email\" id=\"inputEmail\" class=\"form-control\"\n                                value=\"";
echo e($searchCriteria["email"]);
echo "\">\n                        </div>\n                    </div>\n                    <div class=\"col-md-2 col-lg-2 visible-lg\">\n                        <div class=\"form-group\">\n                            <label for=\"inputPhone\">\n                                ";
echo AdminLang::trans("fields.phonenumber");
echo "                            </label>\n                            <input type=\"tel\" name=\"phone\" id=\"inputPhone\" class=\"form-control\"\n                                value=\"";
echo e($searchCriteria["phone"]);
echo "\">\n                        </div>\n                    </div>\n                    <div class=\"col-md-2 col-lg-2 visible-md visible-lg\">\n                        <div class=\"form-group\">\n                            <label for=\"inputGroup\">\n                                ";
echo AdminLang::trans("fields.clientgroup");
echo "                            </label>\n                            <select type=\"text\" name=\"group\" id=\"inputGroup\" class=\"form-control\">\n                                <option value=\"\">\n                                    ";
echo AdminLang::trans("global.any");
echo "                                </option>\n                                ";
foreach ($clientGroups as $groupId => $groupName) {
    echo "<option value=\"" . $groupId . "\"" . ($groupId == $searchCriteria["group"] ? " selected" : "") . ">" . $groupName . "</option>";
}
echo "                            </select>\n                        </div>\n                    </div>\n                    <div class=\"col-md-2 col-lg-2 visible-md visible-lg\">\n                        <div class=\"form-group\">\n                            <label for=\"inputStatus\">\n                                ";
echo AdminLang::trans("fields.status");
echo "                            </label>\n                            <select id=\"inputStatus\" class=\"form-control status\">\n                                <option value=\"any\"\n                                    ";
echo $searchCriteria["status"] == "any" ? "selected" : "";
echo "                                >";
echo AdminLang::trans("global.any");
echo "</option>\n                                ";
foreach ($clientStatuses as $status) {
    echo "<option value=\"" . $status . "\"" . ($status == $searchCriteria["status"] ? " selected" : "") . ">" . $status . "</option>";
}
echo "                            </select>\n                        </div>\n                    </div>\n                    <div class=\"col-xs-6 col-sm-2 col-md-1\">\n                        <label class=\"hidden-xs\">&nbsp;</label>\n                        <button type=\"button\" id=\"btnSearchClientsAdvanced\" class=\"btn btn-default btn-search-advanced btn-block\">\n                            <i class=\"fas fa-plus fa-fw\"></i>\n                            <span class=\"hidden-md\">\n                                ";
echo AdminLang::trans("searchOptions.advanced");
echo "                            </span>\n                        </button>\n                    </div>\n                    <div class=\"col-xs-6 col-sm-2 col-md-1\">\n                        <label class=\"clear-search hidden-xs\">\n                            &nbsp;\n                            <a\n                                href=\"clients.php\"\n                                class=\"";
echo !$searchActive ? " hidden" : "";
echo "\"\n                                title=\"";
echo AdminLang::trans("searchOptions.reset");
echo "\">\n                                <i class=\"fas fa-times fa-fw\"></i>\n                            </a>\n                        </label>\n                        <button type=\"submit\" id=\"btnSearchClients\" class=\"btn btn-primary btn-search btn-block\">\n                            <i class=\"fas fa-search fa-fw\"></i>\n                            <span class=\"hidden-md\">\n                                ";
echo AdminLang::trans("global.search");
echo "                            </span>\n                        </button>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"advanced-search-options\">\n            <div class=\"row\">\n                <div class=\"col-sm-6 col-md-3\">\n                    <div class=\"form-group visible-xs\">\n                        <label for=\"inputEmail2\">\n                            ";
echo AdminLang::trans("fields.email");
echo "                        </label>\n                        <input type=\"text\" name=\"email2\" id=\"inputEmail2\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["email2"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputAddress1\">\n                            ";
echo AdminLang::trans("fields.address1");
echo "                        </label>\n                        <input type=\"text\" name=\"address1\" id=\"inputAddress1\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["address1"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputAddress2\">\n                            ";
echo AdminLang::trans("fields.address2");
echo "                        </label>\n                        <input type=\"text\" name=\"address2\" id=\"inputAddress2\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["address2"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputCity\">\n                            ";
echo AdminLang::trans("fields.city");
echo "                        </label>\n                        <input type=\"text\" name=\"city\" id=\"inputCity\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["city"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputState\">\n                            ";
echo AdminLang::trans("fields.state");
echo "                        </label>\n                        <input type=\"text\" name=\"state\" id=\"inputState\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["state"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputPostcode\">\n                            ";
echo AdminLang::trans("fields.postcode");
echo "                        </label>\n                        <input type=\"text\" name=\"postcode\" id=\"inputPostcode\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["postcode"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputCountry\">\n                            ";
echo AdminLang::trans("fields.country");
echo "                        </label>\n                        <select name=\"country\" id=\"inputCountry\" class=\"form-control\">\n                            <option value=\"\">\n                                ";
echo AdminLang::trans("global.any");
echo "                            </option>\n                            ";
foreach ($countries as $code => $displayName) {
    $selected = "";
    if ($searchCriteria["country"] == $code) {
        $selected = "selected=\"selected\"";
    }
    echo "<option value=\"" . $code . "\" " . $selected . ">" . $displayName . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group hidden-lg\">\n                        <label for=\"inputPhone2\">\n                            ";
echo AdminLang::trans("fields.phonenumber");
echo "                        </label>\n                        <input type=\"text\" name=\"phone2\" id=\"inputPhone2\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["phone2"]);
echo "\">\n                    </div>\n                </div>\n                <div class=\"col-sm-6 col-md-3\">\n                    <div class=\"form-group hidden-md hidden-lg\">\n                        <label for=\"inputGroup2\">\n                            ";
echo AdminLang::trans("fields.clientgroup");
echo "                        </label>\n                        <select type=\"text\" name=\"group2\" id=\"inputGroup2\" class=\"form-control\">\n                            <option value=\"\">\n                                ";
echo AdminLang::trans("searchOptions.anyGroup");
echo "                            </option>\n                            ";
foreach ($clientGroups as $groupId => $groupName) {
    echo "<option value=\"" . $groupId . "\"" . ($groupId == $searchCriteria["group2"] ? " selected" : "") . ">" . $groupName . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputPaymentmethod\">\n                            ";
echo AdminLang::trans("fields.paymentmethod");
echo "                        </label>\n                        <select name=\"paymentmethod\" id=\"inputPaymentmethod\" class=\"form-control\">\n                            <option value=\"\">\n                                ";
echo AdminLang::trans("global.any");
echo "                            </option>\n                            ";
foreach ($paymentMethods as $moduleName => $displayName) {
    echo "<option value=\"" . $moduleName . "\"" . ($moduleName == $searchCriteria["paymentmethod"] ? " selected" : "") . ">" . $displayName . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputCctype\">\n                            ";
echo AdminLang::trans("searchOptions.ccType");
echo "                        </label>\n                        <select name=\"cctype\" id=\"inputCctype\" class=\"form-control\">\n                            <option value=\"\">\n                                ";
echo AdminLang::trans("global.any");
echo "                            </option>\n                            ";
foreach ($cardTypes as $cardType) {
    echo "<option value=\"" . $cardType . "\"" . ($cardType == $searchCriteria["cctype"] ? " selected" : "") . ">" . $cardType . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputCclastfour\">\n                            ";
echo AdminLang::trans("searchOptions.ccLastFour");
echo "                        </label>\n                        <input type=\"text\" name=\"cclastfour\" id=\"inputCclastfour\"\n                            class=\"form-control\" value=\"";
echo e($searchCriteria["cclastfour"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputAutoccbilling\">\n                            ";
echo AdminLang::trans("searchOptions.ccAutoBilling");
echo "                        </label>\n                        <select name=\"autoccbilling\" id=\"inputAutoccbilling\" class=\"form-control\">\n                            ";
foreach ($searchEnabledOptionsInverse as $key => $value) {
    echo "<option value=\"" . $key . "\"" . ((string) $key === $searchCriteria["autoccbilling"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputCredit\">\n                            ";
echo AdminLang::trans("clients.creditbalance");
echo "                        </label>\n                        <input type=\"text\" name=\"credit\" id=\"inputCredit\" class=\"form-control\"\n                            value=\"";
echo e($searchCriteria["credit"]);
echo "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputCurrency\">\n                            ";
echo AdminLang::trans("currencies.currency");
echo "                        </label>\n                        <select name=\"currency\" id=\"inputCurrency\" class=\"form-control\">\n                            <option value=\"\">\n                                ";
echo AdminLang::trans("global.any");
echo "                            </option>\n                            ";
foreach ($currencies as $currencyId => $currencyCode) {
    echo "<option value=\"" . $currencyId . "\"" . ($currencyId == $searchCriteria["currency"] ? " selected" : "") . ">" . $currencyCode . "</option>";
}
echo "                        </select>\n                    </div>\n                </div>\n                <div class=\"col-sm-6 col-md-3\">\n                    <div class=\"form-group\">\n                        <label for=\"inputDateCreated\">\n                            ";
echo AdminLang::trans("searchOptions.signupDate");
echo "                        </label>\n                        <div class=\"form-group date-picker-prepend-icon\">\n                            <label for=\"inputDateCreated\" class=\"field-icon\">\n                                <i class=\"fal fa-calendar-alt\"></i>\n                            </label>\n                            <input id=\"inputDateCreated\"\n                                   type=\"text\"\n                                   name=\"signupdaterange\"\n                                   value=\"";
echo $searchCriteria["signupdaterange"];
echo "\"\n                                   class=\"form-control date-picker-search date-picker-search-100pc\"\n                            />\n                        </div>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputLanguage\">";
echo AdminLang::trans("fields.language");
echo "</label>\n                        <select name=\"language\" id=\"inputLanguage\" class=\"form-control\">\n                            <option value=\"\">\n                                ";
echo AdminLang::trans("global.any");
echo "                            </option>\n                            ";
foreach ($clientLanguages as $language) {
    echo "<option value=\"" . $language . "\"" . ($language == $searchCriteria["language"] ? " selected" : "") . ">" . ucfirst($language) . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputMarketingoptin\">\n                            ";
echo AdminLang::trans("clients.marketingEmailsOptIn");
echo "                        </label>\n                        <select name=\"marketingoptin\" id=\"inputMarketingoptin\" class=\"form-control\">\n                            ";
foreach ($searchEnabledOptions as $key => $value) {
    if ($key === "true") {
        $value = AdminLang::trans("searchOptions.optedIn");
    } else {
        if ($key === "false") {
            $value = AdminLang::trans("searchOptions.optedOut");
        }
    }
    echo "<option value=\"" . $key . "\"" . ($key == $searchCriteria["marketingoptin"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputEmailverification\">\n                            ";
echo AdminLang::trans("searchOptions.emailVerificationStatus");
echo "                        </label>\n                        <select name=\"emailverification\" id=\"inputEmailverification\"\n                            class=\"form-control\">\n                            ";
foreach ($searchEnabledOptions as $key => $value) {
    if ($key === "true") {
        $value = AdminLang::trans("searchOptions.verified");
    } else {
        if ($key === "false") {
            $value = AdminLang::trans("searchOptions.unverified");
        }
    }
    echo "<option value=\"" . $key . "\"" . ($key == $searchCriteria["emailverification"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputAutostatus\">\n                            ";
echo AdminLang::trans("searchOptions.autoStatusUpdate");
echo "                        </label>\n                        <select name=\"autostatus\" id=\"inputAutostatus\" class=\"form-control\">\n                            ";
foreach ($searchEnabledOptionsInverse as $key => $value) {
    echo "<option value=\"" . $key . "\"" . ($key == $searchCriteria["autostatus"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                </div>\n                <div class=\"col-sm-6 col-md-3\">\n                    <div class=\"form-group hidden-md hidden-lg\">\n                        <label for=\"inputStatus2\">\n                            ";
echo AdminLang::trans("fields.status");
echo "                        </label>\n                        <select id=\"inputStatus2\" class=\"form-control status\">\n                            <option value=\"any\"\n                                ";
echo $searchCriteria["status"] == "any" ? "selected" : "";
echo "                            >";
echo AdminLang::trans("searchOptions.anyStatus");
echo "</option>\n                            ";
foreach ($clientStatuses as $status) {
    echo "<option value=\"" . $status . "\"" . ($status == $searchCriteria["status"]) . ">" . $status . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputTaxexempt\">\n                            ";
echo AdminLang::trans("searchOptions.taxExemptStatus");
echo "                        </label>\n                        <select name=\"taxexempt\" id=\"inputTaxexempt\" class=\"form-control\">\n                            ";
foreach ($searchEnabledOptions as $key => $value) {
    if ($key === 1) {
        $value = AdminLang::trans("searchOptions.exempt");
    } else {
        if ($key === 0) {
            $value = AdminLang::trans("searchOptions.nonExempt");
        }
    }
    echo "<option value=\"" . $key . "\"" . ($key == $searchCriteria["taxexempt"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputLatefees\">\n                            ";
echo AdminLang::trans("clients.latefees");
echo "                        </label>\n                        <select name=\"latefees\" id=\"inputLatefees\" class=\"form-control\">\n                            ";
foreach ($searchEnabledOptionsInverse as $key => $value) {
    echo "<option value=\"" . $key . "\"" . ($key == $searchCriteria["latefees"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputOverduenotices\">\n                            ";
echo AdminLang::trans("clients.overduenotices");
echo "                        </label>\n                        <select name=\"overduenotices\" id=\"inputOverduenotices\" class=\"form-control\">\n                            ";
foreach ($searchEnabledOptionsInverse as $key => $value) {
    echo "<option value=\"" . $key . "\"" . ($key == $searchCriteria["overduenotices"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"inputSeparateinvoices\">\n                            ";
echo AdminLang::trans("clients.separateinvoices");
echo "                        </label>\n                        <select name=\"separateinvoices\" id=\"inputSeparateinvoices\"\n                            class=\"form-control\">\n                            ";
foreach ($searchEnabledOptions as $key => $value) {
    echo "<option value=\"" . $key . "\"" . ($key == $searchCriteria["separateinvoices"] ? " selected" : "") . ">" . $value . "</option>";
}
echo "                        </select>\n                    </div>\n                </div>\n                <div class=\"clearfix\"></div>\n                ";
foreach ($customFields as $field) {
    echo "                    <div class=\"col-sm-6 col-md-3\">\n                        <div class=\"form-group\">\n                            <label for=\"inputCf";
    echo $field->id;
    echo "\">\n                                ";
    echo $field->fieldname;
    echo "                            </label>\n                            ";
    if ($field->fieldtype == "dropdown") {
        echo "                                <select name=\"customfields[";
        echo $field->id;
        echo "]\" id=\"inputCf";
        echo $field->id;
        echo "\"\n                                class=\"form-control\">\n                                    <option value=\"\">\n                                        ";
        echo AdminLang::trans("global.any");
        echo "                                    </option>\n                                    ";
        foreach (explode(",", $field->fieldoptions) as $value) {
            echo "<option value=\"" . $value . "\"" . ($value == $searchCriteria["customfields"][$field->id] ? " selected" : "") . ">" . $value . "</option>";
        }
        echo "                                </select>\n                            ";
    } else {
        echo "                                <input type=\"text\" name=\"customfields[";
        echo $field->id;
        echo "]\"\n                                    id=\"inputCf";
        echo $field->id;
        echo "\"\n                                    value=\"";
        echo e($searchCriteria["customfields"][$field->id]);
        echo "\"\n                                    class=\"form-control\">\n                            ";
    }
    echo "                        </div>\n                    </div>\n                ";
}
echo "            </div>\n        </div>\n    </div>\n</form>\n\n";
echo $tableOutput;

?>