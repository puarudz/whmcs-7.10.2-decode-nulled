<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("Create Upgrade/Downgrade Orders", false);
$aInt->requiredFiles(array("orderfunctions", "upgradefunctions", "invoicefunctions", "configoptionsfunctions"));
$response = array();
$response["title"] = AdminLang::trans("services.upgradedowngrade");
$id = App::getFromRequest("id");
$result = select_query("tblhosting", "tblhosting.userid,tblhosting.domain,tblhosting.billingcycle,tblhosting.nextduedate,tblhosting.paymentmethod,tblproducts.id AS pid,tblproducts.name,tblproductgroups.name as groupname", array("tblhosting.id" => $id), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid");
$data = mysql_fetch_array($result);
$userid = $data["userid"];
$service_groupname = $data["groupname"];
$service_pid = $data["pid"];
$service_prodname = $data["name"];
$service_domain = $data["domain"];
$service_billingcycle = $data["billingcycle"];
$service_nextduedate = $data["nextduedate"];
$service_paymentmethod = $data["paymentmethod"];
ob_start();
if (!$userid) {
    $aInt->jsonResponse(array("body" => AdminLang::trans("global.erroroccurred")));
}
$service_nextduedate = str_replace("-", "", $service_nextduedate);
if ($service_billingcycle != "Free Account" && $service_billingcycle != "One Time" && $service_nextduedate < date("Ymd")) {
    infoBox(AdminLang::trans("services.upgradeoverdue"), AdminLang::trans("services.upgradeoverdueinfo"), "error");
    echo $infobox;
    $content = ob_get_contents();
    ob_end_clean();
    $aInt->jsonResponse(array("body" => $content));
}
if (upgradeAlreadyInProgress($id)) {
    $orders = get_query_val("tblupgrades", "orderid", array("status" => "Pending", "relid" => $id));
    infoBox(AdminLang::trans("services.upgradealreadyinprogress"), AdminLang::trans("services.upgradealreadyinprogressinfo") . " <a href='#' id='viewOrder'>" . AdminLang::trans("orders.vieworder") . "</a>", "error");
    echo $infobox;
    echo "<script>\n    jQuery('a#viewOrder').click(function() {\n        window.location = 'orders.php?action=view&id=" . $orders . "';\n        window.close();\n    });\n</script>";
    $content = ob_get_contents();
    ob_end_clean();
    $aInt->jsonResponse(array("body" => $content));
}
$currency = getCurrency($userid);
if ($action == "getcycles") {
    check_token("WHMCS.admin.default");
    ob_start();
    ajax_getcycles($pid);
    $content = ob_get_contents();
    ob_end_clean();
    $aInt->jsonResponse(array("body" => $content));
} else {
    if ($action == "calcsummary") {
        check_token("WHMCS.admin.default");
        ob_start();
        try {
            $_SESSION["uid"] = $userid;
            $promocode = App::getFromRequest("promocode");
            if ($type == "product") {
                $newproductid = App::getFromRequest("newproductid");
                $billingcycle = App::getFromRequest("billingcycle");
                $upgrades = SumUpPackageUpgradeOrder($id, $newproductid, $billingcycle, $promocode, $service_paymentmethod, false);
                $upgrades = $upgrades[0];
                $subtotal = $GLOBALS["subtotal"];
                $qualifies = $GLOBALS["qualifies"];
                $discount = $GLOBALS["discount"];
                $total = formatCurrency($subtotal - $discount);
                echo AdminLang::trans("services.daysleft") . ": " . $upgrades["daysuntilrenewal"] . " / " . $upgrades["totaldays"] . "<br />";
                if (0 < $discount) {
                    echo AdminLang::trans("fields.discount") . ": " . formatCurrency($GLOBALS["discount"]) . "<br />";
                }
                echo AdminLang::trans("services.upgradedue") . ": <span style=\"font-size:16px;\">" . $total . "</span>";
            } else {
                if ($type == "configoptions") {
                    $configoption = App::getFromRequest("configoption");
                    $upgrades = SumUpPackageUpgradeOrder($id, $service_pid, $service_billingcycle, $promocode, $service_paymentmethod, false);
                    $upgrades = $upgrades[0];
                    echo AdminLang::trans("services.daysleft") . ": " . $upgrades["daysuntilrenewal"] . " / " . $upgrades["totaldays"] . "<br />";
                    $upgrades = SumUpConfigOptionsOrder($id, $configoption, $promocode, $service_paymentmethod, false);
                    $subtotal = $GLOBALS["subtotal"];
                    $qualifies = $GLOBALS["qualifies"];
                    $discount = $GLOBALS["discount"];
                    $total = formatCurrency($subtotal - $discount);
                    foreach ($upgrades as $upgrade) {
                        echo $upgrade["configname"] . ": " . $upgrade["originalvalue"] . " => " . $upgrade["newvalue"] . " (" . $upgrade["price"] . ")<br />";
                    }
                    if (0 < $discount) {
                        echo AdminLang::trans("fields.discount") . ": " . formatCurrency($GLOBALS["discount"]) . "<br />";
                    }
                    echo AdminLang::trans("services.upgradedue") . ": <span style=\"font-size:16px;\">" . $total . "</span>";
                }
            }
        } catch (Exception $e) {
            echo Lang::trans("error") . ": " . $e->getMessage();
        }
        unset($_SESSION["uid"]);
        $content = ob_get_contents();
        ob_end_clean();
        $aInt->jsonResponse(array("body" => $content));
    } else {
        if ($action == "order") {
            check_token("WHMCS.admin.default");
            try {
                $_SESSION["uid"] = $userid;
                $promocode = App::getFromRequest("promocode");
                if ($type == "product") {
                    $newproductid = App::getFromRequest("newproductid");
                    $billingcycle = App::getFromRequest("billingcycle");
                    $upgrades = SumUpPackageUpgradeOrder($id, $newproductid, $billingcycle, $promocode, $service_paymentmethod, true);
                } else {
                    if ($type == "configoptions") {
                        $configoption = App::getFromRequest("configoption");
                        $upgrades = SumUpConfigOptionsOrder($id, $configoption, $promocode, $service_paymentmethod, true);
                    }
                }
                $upgradedata = createUpgradeOrder($id, "", $promocode, $service_paymentmethod);
                $orderid = $upgradedata["orderid"];
                unset($_SESSION["uid"]);
                $response["redirect"] = "orders.php?action=view&id=" . $orderid;
            } catch (Exception $e) {
                $error = $e->getMessage();
                $response["body"] = "<alert class=\"alert alert-danger\">\n    " . $error . "\n</alert>";
            }
        } else {
            if (!$action) {
                if (!$type) {
                    $type = "product";
                }
                $configoptions = getCartConfigOptions($service_pid, array(), $service_billingcycle, $id);
                echo "\n    <p>\n        <strong>\n            ";
                echo AdminLang::trans("services.related");
                echo ":\n        </strong>\n        ";
                echo $service_groupname . " - " . $service_prodname;
                if ($service_domain) {
                    echo " (" . $service_domain . ")";
                }
                echo "    </p>\n\n<script>\njQuery(document).ready(function(){\n    calctotals();\n    var thisId = '";
                echo $id;
                echo "';\n\n    jQuery(\"#newpid\").on('change', function () {\n        var newpid = jQuery(\"#newpid option:selected\").val();\n        WHMCS.http.jqClient.post(\n            \"clientsupgrade.php\",\n            {\n                action: \"getcycles\",\n                id: ";
                echo $id;
                echo ",\n                pid: newpid,\n                token: csrfToken\n            },\n        function(data){\n            jQuery(\"#billingcyclehtml\").html(data.body);\n            calctotals();\n        });\n    });\n\n    jQuery('.upgrade-type').on('change', function () {\n        var type = jQuery(this).val();\n        jQuery('#modalAjax .loader').show();\n        WHMCS.http.jqClient.jsonPost(\n            {\n                url: 'clientsupgrade.php',\n                data: 'id=' + thisId + '&type=' + type + '&token=' + csrfToken,\n                success: function(data) {\n                    updateAjaxModal(data);\n                }\n            }\n        );\n    });\n\n});\n\nfunction calctotals() {\n    jQuery('#modalAjax .loader').show();\n    WHMCS.http.jqClient.jsonPost({\n        url: 'clientsupgrade.php',\n        data: 'action=calcsummary&' + jQuery(\"#upgradefrm\").serialize() + '&token=' + csrfToken,\n        success: function(data) {\n            data = data.body;\n            if (!data) {\n                data = '";
                echo AdminLang::trans("services.nochanges");
                echo "';\n            }\n            jQuery(\"#upgradesummary\").html(data);\n        },\n        always: function() {\n            jQuery('#modalAjax .loader').fadeOut();\n        }\n    });\n}\n</script>\n\n<form method=\"post\" action=\"";
                echo $_SERVER["PHP_SELF"];
                echo "?action=order\" id=\"upgradefrm\">\n    ";
                echo generate_token();
                echo "    <input type=\"hidden\" name=\"id\" value=\"";
                echo $id;
                echo "\" />\n    <table class=\"form\">\n        <tr>\n            <td class=\"fieldlabel\" width=\"25%\">\n                ";
                echo AdminLang::trans("services.upgradetype");
                echo "            </td>\n            <td class=\"fieldarea\">\n                ";
                $checked = "";
                if ($type == "product") {
                    $checked = "checked=\"checked\"";
                }
                echo "                <label for=\"typeproduct\">\n                    <input class=\"upgrade-type\"\n                           type=\"radio\"\n                           name=\"type\"\n                           value=\"product\"\n                           id=\"typeproduct\"\n                           ";
                echo $checked;
                echo "                    />\n                    ";
                echo AdminLang::trans("services.productcycle");
                echo "                </label>\n                ";
                if (count($configoptions)) {
                    $configChecked = "";
                    if ($type == "configoptions") {
                        $configChecked = "checked=\"checked\"";
                    }
                    echo "                    <label for=\"typeconfigoptions\">\n                        <input class=\"upgrade-type\"\n                               type=\"radio\"\n                               name=\"type\"\n                               value=\"configoptions\"\n                               id=\"typeconfigoptions\"\n                               ";
                    echo $configChecked;
                    echo "                        />\n                        ";
                    echo AdminLang::trans("setup.configoptions");
                    echo "                    </label>\n                ";
                }
                echo "            </td>\n        </tr>\n        ";
                if ($type == "product") {
                    echo "            <tr>\n                <td class=\"fieldlabel\">\n                    ";
                    echo AdminLang::trans("services.newservice");
                    echo "                </td>\n                <td class=\"fieldarea\">\n                    <select name=\"newproductid\" id=\"newpid\" class=\"form-control select-inline\">\n                        ";
                    echo $aInt->productDropDown($service_pid);
                    echo "                    </select>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">\n                    ";
                    echo AdminLang::trans("fields.billingcycle");
                    echo "                </td>\n                <td class=\"fieldarea\" id=\"billingcyclehtml\">\n                    ";
                    ajax_getcycles($service_pid);
                    echo "                </td>\n            </tr>\n        ";
                } else {
                    if ($type == "configoptions") {
                        foreach ($configoptions as $configoption) {
                            $optionid = $configoption["id"];
                            $optionhidden = $configoption["hidden"];
                            $optionname = $configoption["optionname"];
                            if ($optionhidden) {
                                $optionname .= " <i>" . AdminLang::trans("fields.hidden") . "</i>";
                            }
                            $optiontype = $configoption["optiontype"];
                            $selectedvalue = $configoption["selectedvalue"];
                            $selectedqty = $configoption["selectedqty"];
                            echo "<tr><td class=\"fieldlabel\">" . $optionname . "</td><td class=\"fieldarea\">";
                            if ($optiontype == "1") {
                                $name = "name=\"configoption[" . $optionid . "]\"";
                                $onChange = "onchange=\"calctotals();\"";
                                $class = "class=\"form-control select-inline\"";
                                echo "<select " . $name . " " . $onChange . " " . $class . ">";
                                foreach ($configoption["options"] as $option) {
                                    $opId = $option["id"];
                                    $opName = $option["name"];
                                    $extra = "";
                                    $hidden = "";
                                    if ($selectedvalue == $opId) {
                                        $extra .= " selected=\"selected\"";
                                    }
                                    if ($option["hidden"]) {
                                        $extra .= " style=\"color: #ccc;\"";
                                    }
                                    echo "<option value=\"" . $opId . "\"" . $extra . ">" . $opName . "</option>";
                                }
                                echo "</select>";
                            } else {
                                if ($optiontype == "2") {
                                    foreach ($configoption["options"] as $option) {
                                        $name = "name=\"configoption[" . $optionid . "]\"";
                                        $id = " id=\"configoption-" . $optionid . "-" . $option["id"] . "\" ";
                                        $value = "value=\"" . $option["id"] . "\"";
                                        $selected = "";
                                        if ($selectedvalue == $option["id"]) {
                                            $selected = "checked=\"checked\"";
                                        }
                                        $onClick = "onclick=\"calctotals();\"";
                                        echo "<label for=\"configoption-" . $optionid . "-" . $option["id"] . "\">";
                                        echo "<input type=\"radio\" " . $name . " " . $id . " " . $value . " " . $selected . " " . $onClick . "> ";
                                        $optionValue = (string) $option["name"];
                                        if ($option["hidden"]) {
                                            $optionValue = "<span style=\"color: #ccc;\">" . $optionValue . "</span>";
                                        }
                                        echo $optionValue . "</label><br>";
                                    }
                                } else {
                                    if ($optiontype == "3") {
                                        $selected = "";
                                        if ($selectedqty) {
                                            $selected = "checked=\"checked\"";
                                        }
                                        $type = "type=\"checkbox\"";
                                        $class = "class=\"form-control\"";
                                        $name = "name=\"configoption[" . $optionid . "]\"";
                                        $value = "value=\"1\"";
                                        $onClick = "onclick=\"calctotals()\"";
                                        echo "<input " . $type . " " . $name . " " . $class . " " . $value . " " . $onClick . " " . $selected . ">" . " " . $configoption["options"][0]["name"];
                                    } else {
                                        if ($optiontype == "4") {
                                            $type = "type=\"text\"";
                                            $class = "class=\"form-control input-75\"";
                                            $name = "name=\"configoption[" . $optionid . "]\"";
                                            $value = "value=\"" . $selectedqty . "\"\"";
                                            $onClick = "onkeyup=\"calctotals()\"";
                                            echo "<input " . $type . " " . $name . " " . $value . " " . $class . " " . $onClick . ">" . " x " . $configoption["options"][0]["name"];
                                        }
                                    }
                                }
                            }
                            echo "</td></tr>";
                        }
                    }
                }
                echo "        <tr>\n            <td class=\"fieldlabel\">\n                ";
                echo AdminLang::trans("fields.promocode");
                echo "            </td>\n            <td class=\"fieldarea\">\n                <select name=\"promocode\"\n                        id=\"promocode\"\n                        class=\"form-control select-inline\"\n                        onchange=\"calctotals();\"\n                >\n                    <option value=\"\">\n                        ";
                echo AdminLang::trans("global.none");
                echo "                    </option>\n                    ";
                $promoid = App::getFromRequest("promoid");
                $result = select_query("tblpromotions", "", array("upgrades" => "1"), "code", "ASC");
                while ($data = mysql_fetch_array($result)) {
                    $promo_id = $data["id"];
                    $promo_code = $data["code"];
                    $promo_type = $data["type"];
                    $promo_recurring = $data["recurring"];
                    $promo_value = $data["value"];
                    if ($promo_type == "Percentage") {
                        $promo_value .= "%";
                    } else {
                        $promo_value = formatCurrency($promo_value);
                    }
                    if ($promo_type == "Free Setup") {
                        $promo_value = AdminLang::trans("promos.freesetup");
                    }
                    $promo_recurring = $promo_recurring ? AdminLang::trans("status.recurring") : AdminLang::trans("status.onetime");
                    if ($promo_type == "Price Override") {
                        $promo_recurring = AdminLang::trans("promos.priceoverride");
                    }
                    if ($promo_type == "Free Setup") {
                        $promo_recurring = "";
                    }
                    $selected = "";
                    if ($promo_id == $promoid) {
                        $selected = "selected=\"selected\"";
                    }
                    echo "<option value=\"" . $promo_code . "\" " . $selected . ">" . (string) $promo_code . " - " . $promo_value . " " . $promo_recurring . "</option>";
                }
                echo "                </select>\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
                echo AdminLang::trans("services.upgradesummary");
                echo "            </td>\n            <td class=\"fieldarea\" id=\"upgradesummary\">\n                ";
                echo AdminLang::trans("services.upgradesummaryinfo");
                echo "            </td>\n        </tr>\n    </table>\n</form>\n    ";
                $response["submitlabel"] = AdminLang::trans("orders.createorder");
                $response["submitId"] = "btnCreateUpgrade";
            }
        }
    }
}
$content = ob_get_contents();
ob_end_clean();
$response["body"] = $content;
$aInt->jsonResponse($response);
function ajax_getcycles($pid)
{
    global $aInt;
    global $service_billingcycle;
    $pricing = getPricingInfo($pid);
    if ($pricing["type"] == "recurring") {
        echo "<select name=\"billingcycle\" class=\"form-control select-inline\" onchange=\"calctotals()\">";
        if ($pricing["monthly"]) {
            $selected = "";
            if ($service_billingcycle == "Monthly") {
                $selected = " selected=\"selected\"";
            }
            echo "<option value=\"monthly\"" . $selected . ">" . $pricing["monthly"] . "</option>";
        }
        if ($pricing["quarterly"]) {
            $selected = "";
            if ($service_billingcycle == "Quarterly") {
                $selected = " selected=\"selected\"";
            }
            echo "<option value=\"quarterly\"" . $selected . ">" . $pricing["quarterly"] . "</option>";
        }
        if ($pricing["semiannually"]) {
            $selected = "";
            if ($service_billingcycle == "Semi-Annually") {
                $selected = " selected=\"selected\"";
            }
            echo "<option value=\"semiannually\"" . $selected . ">" . $pricing["semiannually"] . "</option>";
        }
        if ($pricing["annually"]) {
            $selected = "";
            if ($service_billingcycle == "Annually") {
                $selected = " selected=\"selected\"";
            }
            echo "<option value=\"annually\"" . $selected . ">" . $pricing["annually"] . "</option>";
        }
        if ($pricing["biennially"]) {
            $selected = "";
            if ($service_billingcycle == "Biennially") {
                $selected = " selected=\"selected\"";
            }
            echo "<option value=\"biennially\"" . $selected . ">" . $pricing["biennially"] . "</option>";
        }
        if ($pricing["triennially"]) {
            $selected = "";
            if ($service_billingcycle == "Triennially") {
                $selected = " selected=\"selected\"";
            }
            echo "<option value=\"triennially\"" . $selected . ">" . $pricing["triennially"] . "</option>";
        }
        echo "</select>";
    } else {
        if ($pricing["type"] == "onetime") {
            echo "<input type=\"hidden\" name=\"billingcycle\" value=\"onetime\" /> " . AdminLang::trans("billingcycles.onetime");
        } else {
            echo "<input type=\"hidden\" name=\"billingcycle\" value=\"free\" /> " . AdminLang::trans("billingcycles.free");
        }
    }
}

?>