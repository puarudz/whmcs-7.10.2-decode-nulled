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
$aInt = new WHMCS\Admin("Link Tracking");
$aInt->title = AdminLang::trans("utilities.linkTracking.title");
$aInt->sidebar = "utilities";
$aInt->icon = "linktracking";
$aInt->helplink = "Link Tracking";
$id = App::getFromRequest("id");
if ($action == "save") {
    check_token("WHMCS.admin.default");
    $streamPattern = "/^[a-zA-Z0-9]+\\s?:\\s?\\//";
    if (!preg_match($streamPattern, $url)) {
        redir("action=manage&id=" . $id . "&invalidurl=1");
    }
    $name = App::getFromRequest("name");
    $url = App::getFromRequest("url");
    $clicks = (int) App::getFromRequest("clicks");
    $conversions = (int) App::getFromRequest("conversions");
    if ($id) {
        $table = "tbllinks";
        $array = array("name" => $name, "link" => $url, "clicks" => $clicks, "conversions" => $conversions);
        $where = array("id" => $id);
        update_query($table, $array, $where);
    } else {
        $table = "tbllinks";
        $array = array("name" => $name, "link" => $url, "clicks" => $clicks, "conversions" => $conversions);
        insert_query($table, $array);
    }
    redir();
}
if ($action == "delete") {
    check_token("WHMCS.admin.default");
    delete_query("tbllinks", array("id" => $id));
    redir();
}
ob_start();
if (!$action) {
    $aInt->deleteJSConfirm("doDelete", "linktracking", "delete", "?action=delete&id=");
    echo "\n    <p>";
    echo AdminLang::trans("utilities.linkTracking.description");
    echo "</p>\n\n    <p>\n        <a href=\"";
    echo $whmcs->getPhpSelf();
    echo "?action=manage\" class=\"btn btn-default\">\n            <i class=\"fas fa-plus\"></i> ";
    echo AdminLang::trans("utilities.linkTracking.addNew");
    echo "        </a>\n    </p>\n\n    ";
    if ($orderby == "conversionrate") {
        $orderbysql = "(conversions/clicks)";
    } else {
        if (in_array($orderby, array("id", "name", "link", "clicks", "conversions"))) {
            $orderbysql = $orderby;
        } else {
            $orderby = "";
            $orderbysql = "id";
        }
    }
    $aInt->sortableTableInit("id", "ASC");
    $result = full_query("SELECT COUNT(id) FROM tbllinks");
    $data = mysql_fetch_array($result);
    $numrows = $data[0];
    $result = full_query("SELECT * FROM tbllinks ORDER BY " . db_escape_string($orderbysql) . " " . db_escape_string($order) . " LIMIT " . (int) ($page * $limit) . "," . (int) $limit);
    $editText = AdminLang::trans("global.edit");
    $deleteText = AdminLang::trans("global.delete");
    while ($data = mysql_fetch_array($result)) {
        $id = $data["id"];
        $name = $data["name"];
        $link = $data["link"];
        $clicks = $data["clicks"];
        $conversions = $data["conversions"];
        $displaylink = $link;
        if (40 < strlen($displaylink)) {
            $displaylink = substr($link, 0, 40) . "...";
        }
        $conversionrate = @round($conversions / $clicks * 100, 2);
        $editLink = "<a href=\"?action=manage&id=" . $id . "\">" . "<img src=\"images/edit.gif\" width=\"16\" height=\"16\" alt=\"" . $editText . "\"></a>";
        $deleteLink = "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\">" . "<img src=\"images/delete.gif\" width=\"16\" height=\"16\" alt=\"" . $deleteText . "\"></a>";
        $tabledata[] = array($id, $name, "<a href=\"" . $link . "\" target=\"_blank\">" . $displaylink . "</a>", $clicks, $conversions, $conversionrate . "%", $editLink, $deleteLink);
    }
    echo $aInt->sortableTable(array(array("id", AdminLang::trans("fields.id")), array("name", AdminLang::trans("fields.id")), array("link", AdminLang::trans("utilities.linkTracking.link")), array("clicks", AdminLang::trans("utilities.linkTracking.clicks")), array("conversions", AdminLang::trans("utilities.linkTracking.conversions")), array("conversionrate", AdminLang::trans("utilities.linkTracking.conversionRate")), "", ""), $tabledata);
} else {
    if ($action == "manage") {
        if ($id) {
            $table = "tbllinks";
            $fields = "";
            $where = array("id" => $id);
            $result = select_query($table, $fields, $where);
            $data = mysql_fetch_array($result);
            $id = $data["id"];
            $name = $data["name"];
            $url = $data["link"];
            $clicks = $data["clicks"];
            $conversions = $data["conversions"];
            $actiontitle = AdminLang::trans("utilities.linkTracking.editLink");
        } else {
            $clicks = 0;
            $conversions = 0;
            $actiontitle = AdminLang::trans("utilities.linkTracking.addLink");
        }
        if ($whmcs->get_req_var("invalidurl")) {
            infoBox(AdminLang::trans("utilities.linkTracking.invalidUrl"), AdminLang::trans("utilities.linkTracking.invalidUrlDescription"));
            echo $infobox;
        }
        $idString = "";
        if ($id) {
            $idString = "&id=" . $id;
        }
        echo "\n<form method=\"post\" class=\"form-horizontal\" action=\"";
        echo App::getPhpSelf();
        echo "?action=save";
        echo $idString;
        echo "\">\n    <div class=\"admin-tabs-v2\">\n        <ul class=\"nav nav-tabs admin-tabs\" role=\"tablist\">\n            <li class=\"active\" role=\"presentation\">\n                <a id=\"tabLinks\" data-toggle=\"tab\" href=\"#contentLinks\" role=\"tab\">\n                    ";
        echo $actiontitle;
        echo "                </a>\n            </li>\n        </ul>\n        <div class=\"tab-content\">\n            <div class=\"tab-pane active\" id=\"contentLinks\">\n                <div class=\"form-group\">\n                    <label for=\"inputName\" class=\"col-md-4 col-sm-6 control-label\">\n                        ";
        echo AdminLang::trans("fields.name");
        echo "<br>\n                        <small>\n                            ";
        echo AdminLang::trans("utilities.linkTracking.nameDescription");
        echo "                        </small>\n                    </label>\n                    <div class=\"col-md-8 col-sm-6\">\n                        <input type=\"text\"\n                               id=\"inputName\"\n                               class=\"form-control input-300\"\n                               name=\"name\"\n                               value=\"";
        echo $name;
        echo "\"\n                        />\n                    </div>\n                </div>\n                <div class=\"form-group\">\n                    <label for=\"inputUrl\" class=\"col-md-4 col-sm-6 control-label\">\n                        ";
        echo AdminLang::trans("utilities.linkTracking.forwardTo");
        echo "<br>\n                        <small>\n                            ";
        echo AdminLang::trans("utilities.linkTracking.forwardToDescription");
        echo "                        </small>\n                    </label>\n                    <div class=\"col-md-8 col-sm-6\">\n                        <input type=\"url\"\n                               id=\"inputUrl\"\n                               class=\"form-control input-700\"\n                               name=\"url\"\n                               value=\"";
        echo $url;
        echo "\"\n                        />\n                    </div>\n                </div>\n                <div class=\"form-group\">\n                    <label for=\"inputClicks\" class=\"col-md-4 col-sm-6 control-label\">\n                        ";
        echo AdminLang::trans("utilities.linkTracking.clicks");
        echo "<br>\n                        <small>\n                            ";
        echo AdminLang::trans("utilities.linkTracking.clicksDescription");
        echo "                        </small>\n                    </label>\n                    <div class=\"col-md-8 col-sm-6\">\n                        <input type=\"number\"\n                               id=\"inputClicks\"\n                               class=\"form-control input-100\"\n                               name=\"clicks\"\n                               value=\"";
        echo $clicks;
        echo "\"\n                               step=\"1\"\n                               min=\"0\"\n                        />\n                    </div>\n                </div>\n                <div class=\"form-group\">\n                    <label for=\"inputConversions\" class=\"col-md-4 col-sm-6 control-label\">\n                        ";
        echo AdminLang::trans("utilities.linkTracking.conversions");
        echo "<br>\n                        <small>\n                            ";
        echo AdminLang::trans("utilities.linkTracking.conversionDescription");
        echo "                        </small>\n                    </label>\n                    <div class=\"col-md-8 col-sm-6\">\n                        <input type=\"number\"\n                               id=\"inputConversions\"\n                               class=\"form-control input-100\"\n                               name=\"conversions\"\n                               value=\"";
        echo $conversions;
        echo "\"\n                               step=\"1\"\n                               min=\"0\"\n                        />\n                    </div>\n                </div>\n                ";
        if ($id) {
            $linkUrl = WHMCS\Config\Setting::getValue("SystemURL") . "/link.php?id=" . $id;
            $copyToClipboard = AdminLang::trans("global.clipboardCopy");
            $linkDescription = AdminLang::trans("utilities.linkTracking.linkUrlDescription");
            echo "                    <div class=\"form-group\">\n                        <label for=\"inputLinkUrl\" class=\"col-md-4 col-sm-6 control-label\">\n                            ";
            echo AdminLang::trans("utilities.linkTracking.linkUrl");
            echo "<br>\n                            <small>\n                                ";
            echo $linkDescription;
            echo "                            </small>\n                        </label>\n                        <div class=\"col-md-8 col-sm-6\">\n                            <div class=\"input-group input-group-flex\">\n                                <input id=\"inputLinkUrl\"\n                                       type=\"text\"\n                                       name=\"linkurl\"\n                                       readonly=\"readonly\"\n                                       class=\"form-control input-700\"\n                                       value=\"";
            echo $linkUrl;
            echo "\"\n                                />\n                                <span class=\"input-group-btn\">\n                            <button class=\"btn btn-default copy-to-clipboard\"\n                                    data-clipboard-target=\"#inputLinkUrl\"\n                                    type=\"button\"\n                            >\n                                <i class=\"fal fa-copy\" title=\"";
            echo $copyToClipboard;
            echo ">\"></i>\n                                <span class=\"sr-only\">";
            echo $copyToClipboard;
            echo "></span>\n                            </button>\n                        </span>\n                            </div>\n                        </div>\n                    </div>\n                ";
        }
        echo "            </div>\n        </div>\n    </div>\n    <hr>\n    <div class=\"text-center\">\n        <button type=\"submit\" class=\"btn btn-primary\">\n            ";
        echo AdminLang::trans("global.save");
        echo "        </button>\n        <button type=\"reset\" class=\"btn btn-default\" onclick=\"window.location='utilitieslinktracking.php';\">\n            ";
        echo AdminLang::trans("global.cancel");
        echo "        </button>\n    </div>\n</form>\n    ";
    }
}
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();

?>