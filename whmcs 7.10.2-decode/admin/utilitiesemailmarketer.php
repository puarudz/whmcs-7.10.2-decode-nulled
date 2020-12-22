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
$aInt = new WHMCS\Admin("Email Marketer");
$aInt->title = "Email Marketer";
$aInt->sidebar = "utilities";
$aInt->icon = "emailmarketer";
$aInt->helplink = "Email Marketer";
if ($action == "delete") {
    check_token("WHMCS.admin.default");
    delete_query("tblemailmarketer", array("id" => $id));
    redir();
}
ob_start();
$aInt->deleteJSConfirm("doDelete", "emailmarketer", "delete", "?action=delete&id=");
echo "\n<p>";
echo AdminLang::trans("utilities.emailMarketer.description");
echo "</p>\n\n<p>\n    <a href=\"";
echo routePath("admin-utilities-tools-email-marketer-rule");
echo "\"\n       id=\"btnCreateRule\"\n       class=\"btn btn-default open-modal\"\n       data-btn-submit-id=\"btnSaveEmailMarketingRule\"\n       data-btn-submit-label=\"";
echo AdminLang::trans("global.save");
echo "\"\n       data-modal-class=\"email-marketer-rule\"\n       data-modal-title=\"";
echo AdminLang::trans("utilities.emailMarketer.createNew");
echo "\"\n    >\n        <i class=\"fas fa-plus\"></i> ";
echo AdminLang::trans("utilities.emailMarketer.createNew");
echo "    </a>\n</p>\n\n";
$aInt->sortableTableInit("name", "ASC");
$result = select_query("tblemailmarketer", "COUNT(*)", "");
$data = mysql_fetch_array($result);
$numrows = $data[0];
$editText = AdminLang::trans("global.edit");
$submitLabel = AdminLang::trans("global.save");
$editRule = AdminLang::trans("utilities.emailMarketer.manageRule");
$disabledImage = "<img src=\"images/icons/disabled.png\" " . "alt=\"" . AdminLang::trans("utilities.emailMarketer.disabled") . "\">";
$enabledImage = "<img src=\"images/icons/tick.png\" alt=\"" . AdminLang::trans("status.active") . "\">";
$result = select_query("tblemailmarketer", "", "", $orderby, $order, $page * $limit . "," . $limit);
while ($data = mysql_fetch_array($result)) {
    $id = $data["id"];
    $name = $data["name"];
    $type = $data["type"];
    $disable = $data["disable"];
    $marketing = $data["marketing"];
    $createdAt = fromMySQLDate($data["created_at"], true, true);
    if (substr($createdAt, 0, 10) == "0000-00-00") {
        $createdAt = "-";
    }
    $updatedAt = fromMySQLDate($data["updated_at"], true, true);
    if (substr($updatedAt, 0, 10) == "0000-00-00") {
        $updatedAt = "-";
    }
    $type = $type == "client" ? "Client" : "Product/Service";
    $disable = $disable ? $disabledImage : $enabledImage;
    $route = routePath("admin-utilities-tools-email-marketer-rule", $id);
    $editLink = "<a href=\"" . $route . "\"\n       class=\"open-modal\"\n       data-btn-submit-id=\"btnSaveEmailMarketingRule\"\n       data-btn-submit-label=\"" . $submitLabel . "\"\n       data-modal-class=\"email-marketer-rule\"\n       data-modal-title=\"" . $editRule . "\"\n    >\n        <img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $editText . "\">\n    </a>";
    $tabledata[] = array($id, $name, $type, "<div class=\"text-center\">" . $disable . "</a>", $createdAt, $updatedAt, $editLink, "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>");
}
echo $aInt->sortableTable(array(AdminLang::trans("fields.id"), AdminLang::trans("fields.name"), AdminLang::trans("fields.type"), array("", AdminLang::trans("fields.enabled"), "70"), array("", AdminLang::trans("fields.created"), "160"), array("", AdminLang::trans("fields.updated"), "160"), "", ""), $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();

?>