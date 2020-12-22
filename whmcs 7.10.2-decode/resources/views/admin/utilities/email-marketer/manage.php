<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<div class=\"alert alert-danger admin-modal-error\" style=\"display: none\"></div>\n<form action=\"";
echo routePath("admin-utilities-tools-email-marketer-rule-save");
echo "\" method=\"post\">\n    ";
echo generate_token();
echo "    <input type=\"hidden\" name=\"id\" value=\"";
echo $id;
echo "\">\n    <table class=\"form table table-condensed\">\n        <tbody>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
echo AdminLang::trans("utilities.emailMarketer.ruleName");
echo "            </td>\n            <td class=\"fieldarea\">\n                <input type=\"text\" name=\"name\" value=\"";
echo $ruleName;
echo "\" class=\"form-control input-inline input-200\">\n                ";
echo AdminLang::trans("utilities.emailMarketer.internalUseOnly");
echo "            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
echo AdminLang::trans("utilities.emailMarketer.ruleType");
echo "            </td>\n            <td class=\"fieldarea\">\n                <label class=\"radio-inline\">\n                    <input type=\"radio\"\n                           name=\"type\"\n                           value=\"client\"\n                           class=\"toggle-display\"\n                           data-show=\"client-criteria\"\n                        ";
echo $type == "client" ? "checked=\"checked\"" : "";
echo "                    >\n                    ";
echo AdminLang::trans("utilities.emailMarketer.ruleTypeClient");
echo "                </label>\n                <label class=\"radio-inline\">\n                    <input type=\"radio\"\n                           name=\"type\"\n                           value=\"product\"\n                           class=\"toggle-display\"\n                           data-show=\"service-criteria\"\n                        ";
echo $type == "product" ? "checked=\"checked\"" : "";
echo "                    >\n                    ";
echo AdminLang::trans("utilities.emailMarketer.ruleTypeService");
echo "                </label>\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
echo AdminLang::trans("utilities.emailMarketer.marketing");
echo "            </td>\n            <td class=\"fieldarea\">\n                <label>\n                    <input type=\"hidden\" name=\"marketing\" value=\"0\">\n                    <input type=\"checkbox\"\n                           name=\"marketing\"\n                           value=\"1\"\n                        ";
echo $marketing ? " checked=\"checked\"" : "";
echo "                    >\n                    ";
echo AdminLang::trans("utilities.emailMarketer.marketingDescription");
echo "                </label>\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
echo AdminLang::trans("utilities.emailMarketer.disabled");
echo "            </td>\n            <td class=\"fieldarea\">\n                <label>\n                    <input type=\"hidden\" name=\"disabled\" value=\"0\">\n                    <input type=\"checkbox\"\n                           name=\"disabled\"\n                           value=\"1\"\n                        ";
echo $disabled ? "checked=\"checked\"" : "";
echo "                    >\n                    ";
echo AdminLang::trans("utilities.emailMarketer.disabledDescription");
echo "                </label>\n            </td>\n        </tr>\n        </tbody>\n    </table>\n    <h2>\n        ";
echo AdminLang::trans("utilities.emailMarketer.criteria");
echo "<br>\n        <small>\n            ";
echo AdminLang::trans("utilities.emailMarketer.criteriaDescription1");
echo "            <br>\n            ";
echo AdminLang::trans("utilities.emailMarketer.criteriaDescription2");
echo "        </small>\n    </h2>\n    <div class=\"toggleable client-criteria";
echo $type != "client" ? " hidden" : "";
echo "\">\n        <table class=\"form table table-condensed\">\n            <tbody>\n            <tr>\n                <td class=\"fieldlabel\">\n                    ";
echo AdminLang::trans("utilities.emailMarketer.client.daysSinceRegistration");
echo "                </td>\n                <td class=\"fieldarea\">\n                    <input type=\"number\"\n                           name=\"client_days\"\n                           value=\"";
echo 0 <= $clientDays ? $clientDays : "";
echo "\"\n                           step=\"1\"\n                           min=\"0\"\n                           class=\"form-control input-75\"\n                    >\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">\n                    ";
echo AdminLang::trans("utilities.emailMarketer.client.minimumServices");
echo "                </td>\n                <td class=\"fieldarea\">\n                    <input type=\"number\"\n                           name=\"min_services\"\n                           value=\"";
echo 0 <= $minimumServices ? $minimumServices : "";
echo "\"\n                           step=\"1\"\n                           min=\"0\"\n                           class=\"form-control input-75\"\n                    >\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">\n                    ";
echo AdminLang::trans("utilities.emailMarketer.client.maximumServices");
echo "                </td>\n                <td class=\"fieldarea\">\n                    <input type=\"number\"\n                           name=\"max_services\"\n                           value=\"";
echo 0 <= $maximumServices ? $maximumServices : "";
echo "\"\n                           step=\"1\"\n                           min=\"0\"\n                           class=\"form-control input-75\"\n                    >\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">\n                    ";
echo AdminLang::trans("utilities.emailMarketer.emailTemplates");
echo "                </td>\n                <td class=\"fieldarea\">\n                    <label>\n                        <select name=\"email_template_client\" class=\"form-control\">\n                            ";
foreach ($clientEmailTemplates as $template) {
    echo "                                <option value=\"";
    echo $template->id;
    echo "\"\n                                    ";
    echo $clientEmailTemplate == $template->id ? "selected=\"selected\"" : "";
    echo "                                >\n                                    ";
    echo $template->name;
    echo "                                </option>\n                            ";
}
echo "                        </select>\n                    </label>\n                </td>\n            </tr>\n            </tbody>\n        </table>\n    </div>\n    <div class=\"toggleable service-criteria";
echo $type == "client" ? " hidden" : "";
echo "\">\n        <table class=\"form table table-condensed\">\n            <tbody>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.service.productService");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <label>\n                            <select name=\"products[]\" class=\"form-control\" multiple=\"multiple\">\n                                ";
foreach ($products as $product) {
    echo "                                    <option value=\"";
    echo $product->id;
    echo "\"\n                                        ";
    echo in_array($product->id, $selectedProducts) ? "selected=\"selected\"" : "";
    echo "                                    >\n                                        ";
    echo $product->productGroup->name . " - " . $product->name;
    echo "                                    </option>\n                                ";
}
echo "                            </select>\n                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.service.productAddon");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <label>\n                            <select name=\"addons[]\" class=\"form-control\" multiple=\"multiple\">\n                                ";
foreach ($addons as $addon) {
    echo "                                    <option value=\"";
    echo $addon->id;
    echo "\"\n                                        ";
    echo in_array($addon->id, $selectedAddons) ? "selected=\"selected\"" : "";
    echo "                                    >\n                                        ";
    echo $addon->name;
    echo "                                    </option>\n                                ";
}
echo "                            </select>\n                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.service.status");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <label>\n                            <select name=\"product_status[]\" class=\"form-control\" multiple=\"multiple\">\n                                ";
foreach (WHMCS\Utility\Status::SERVICE_STATUSES as $status) {
    echo "                                    <option value=\"";
    echo $status;
    echo "\"\n                                        ";
    echo in_array($status, $selectedStatuses) ? "selected=\"selected\"" : "";
    echo "                                    >\n                                        ";
    echo AdminLang::trans("status." . strtolower($status));
    echo "                                    </option>\n                                ";
}
echo "                            </select>\n                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.service.cycle");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <label>\n                            <select name=\"product_cycle[]\" class=\"form-control\" multiple=\"multiple\">\n                                ";
foreach ($cycles as $cycle) {
    if ($cycle == "Free Account") {
        $cycle = "Free";
    }
    echo "                                    <option value=\"";
    echo $cycle;
    echo "\"\n                                        ";
    echo in_array($cycle, $selectedCycles) ? "selected=\"selected\"" : "";
    echo "                                    >\n                                        ";
    echo AdminLang::trans("billingcycles." . str_replace(array(" ", "-"), "", strtolower($cycle)));
    echo "                                    </option>\n                                ";
}
echo "                            </select>\n                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.service.numberOfDays");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <input type=\"number\"\n                               name=\"number_of_days\"\n                               value=\"";
echo 0 <= $numberOfDays ? $numberOfDays : "";
echo "\"\n                               step=\"1\"\n                               class=\"form-control input-inline input-75\"\n                        >\n                        <label>\n                            <select name=\"days_type\" class=\"form-control\">\n                                <option value=\"after_order\"";
echo $daysType == "after_order" ? "selected=\"selected\"" : "";
echo ">\n                                    ";
echo AdminLang::trans("utilities.emailMarketer.service.afterOrder");
echo "                                </option>\n                                <option value=\"before_due\"";
echo $daysType == "before_due" ? "selected=\"selected\"" : "";
echo ">\n                                    ";
echo AdminLang::trans("utilities.emailMarketer.service.beforeDue");
echo "                                </option>\n                                <option value=\"after_due\"";
echo $daysType == "after_due" ? "selected=\"selected\"" : "";
echo ">\n                                    ";
echo AdminLang::trans("utilities.emailMarketer.service.afterDue");
echo "                                </option>\n                            </select>\n                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.service.withoutProduct");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <label>\n                            <select name=\"without_product[]\" class=\"form-control\" multiple=\"multiple\">\n                                ";
foreach ($products as $product) {
    echo "                                    <option value=\"";
    echo $product->id;
    echo "\"\n                                        ";
    echo in_array($product->id, $withoutProducts) ? "selected=\"selected\"" : "";
    echo "                                    >\n                                        ";
    echo $product->name;
    echo "                                    </option>\n                                ";
}
echo "                            </select>\n                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.service.withoutAddon");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <label>\n                            <select name=\"without_addon[]\" class=\"form-control\" multiple=\"multiple\">\n                                ";
foreach ($addons as $addon) {
    echo "                                    <option value=\"";
    echo $addon->id;
    echo "\"\n                                        ";
    echo in_array($addon->id, $withoutAddons) ? "selected=\"selected\"" : "";
    echo "                                    >\n                                        ";
    echo $addon->name;
    echo "                                    </option>\n                                ";
}
echo "                            </select>\n                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">\n                        ";
echo AdminLang::trans("utilities.emailMarketer.emailTemplates");
echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <label>\n                            <select name=\"email_template_product\" class=\"form-control\">\n                                ";
foreach ($productEmailTemplates as $template) {
    echo "                                    <option value=\"";
    echo $template->id;
    echo "\"\n                                        ";
    echo $productEmailTemplate == $template->id ? "selected=\"selected\"" : "";
    echo "                                    >\n                                        ";
    echo $template->name;
    echo "                                    </option>\n                                ";
}
echo "                            </select>\n                        </label>\n                    </td>\n                </tr>\n            </tbody>\n        </table>\n    </div>\n</form>\n";

?>