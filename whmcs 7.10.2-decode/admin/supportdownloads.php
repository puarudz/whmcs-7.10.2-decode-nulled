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
require dirname(__DIR__) . DIRECTORY_SEPARATOR . "init.php";
$whmcs = App::self();
$aInt = new WHMCS\Admin("Manage Downloads");
$aInt->title = $aInt->lang("support", "downloads");
$aInt->sidebar = "support";
$aInt->icon = "downloads";
$catid = (int) $whmcs->get_req_var("catid");
$adddownload = $whmcs->get_req_var("adddownload");
$action = $whmcs->get_req_var("action");
$sub = $whmcs->get_req_var("sub");
$addcategory = $whmcs->get_req_var("addcategory");
$remoteDownload = $whmcs->get_req_var("remoteDownload");
$description = $whmcs->get_req_var("description");
$defaultTabOpen = false;
if ($adddownload == "true") {
    check_token("WHMCS.admin.default");
    $filename = $whmcs->get_req_var("filename");
    $filetype = $whmcs->get_req_var("filetype");
    $title = $whmcs->get_req_var("title");
    $type = $whmcs->get_req_var("type");
    if ($filetype != "upload" && parse_url($filename, PHP_URL_SCHEME)) {
        redir("catid=" . $catid . "&tab=2&remoteDownload=1");
    }
    if ($filetype == "upload") {
        foreach (WHMCS\File\Upload::getUploadedFiles("uploadfile") as $uploadedFile) {
            try {
                $filename = $uploadedFile->storeAsDownload();
                break;
            } catch (Exception $e) {
                $aInt->gracefulExit("Could not save file: " . $e->getMessage());
            }
        }
    }
    if (!$title) {
        $title = $filename;
    }
    $hidden = (int) (bool) $whmcs->get_req_var("hidden");
    $clientsonly = (int) (bool) $whmcs->get_req_var("clientsonly");
    $productdownload = (int) (bool) $whmcs->get_req_var("productdownload");
    insert_query("tbldownloads", array("category" => $catid, "type" => $type, "title" => $title, "description" => $description, "location" => $filename, "clientsonly" => $clientsonly, "hidden" => $hidden, "productdownload" => $productdownload));
    logActivity("Added New Download - " . $title);
    redir("catid=" . $catid);
}
ob_start();
$downloadsStorage = Storage::downloads();
if ($downloadsStorage->isLocalAdapter()) {
    $localAdapter = $downloadsStorage->getAdapter();
    if (!is_writable($localAdapter->getPathPrefix())) {
        infoBox($aInt->lang("support", "permissionswarn"), $aInt->lang("support", "permissionswarninfo"));
        echo $infobox;
        $error = "1";
    }
}
if ($remoteDownload) {
    $defaultTabOpen = true;
}
if ($action == "") {
    if ($sub == "save") {
        check_token("WHMCS.admin.default");
        $id = (int) $whmcs->get_req_var("id");
        $location = $whmcs->get_req_var("location");
        if (parse_url($location, PHP_URL_SCHEME)) {
            redir("action=edit&id=" . $id . "&remoteDownload=1");
        }
        $hidden = (int) (bool) $whmcs->get_req_var("hidden");
        $clientsonly = (int) (bool) $whmcs->get_req_var("clientsonly");
        $productdownload = (int) (bool) $whmcs->get_req_var("productdownload");
        update_query("tbldownloads", array("category" => $category, "type" => $type, "title" => $title, "description" => $description, "downloads" => $downloads, "location" => $location, "clientsonly" => $clientsonly, "hidden" => $hidden, "productdownload" => $productdownload), array("id" => $id));
        logActivity("Modified Download (ID: " . $id . ")");
        redir("catid=" . $catid);
    }
    if ($sub == "savecat") {
        check_token("WHMCS.admin.default");
        $hidden = (int) (bool) $hidden;
        update_query("tbldownloadcats", array("name" => $name, "description" => $description, "hidden" => $hidden, "parentid" => $parentcategory), array("id" => $id));
        logActivity("Modified Download (ID: " . $id . ")");
        redir("catid=" . $catid);
    }
    if ($addcategory == "true") {
        check_token("WHMCS.admin.default");
        $hidden = (int) (bool) $whmcs->get_req_var("hidden");
        insert_query("tbldownloadcats", array("parentid" => $catid, "name" => $catname, "description" => $description, "hidden" => $hidden));
        logActivity("Added New Download Category - " . $catname);
        redir("catid=" . $catid);
    }
    if ($sub == "delete") {
        check_token("WHMCS.admin.default");
        $result = select_query("tbldownloads", "id,location", array("id" => $id));
        $data = mysql_fetch_array($result);
        $id = $data["id"];
        if (!$id) {
            redir("catid=" . $catid);
        }
        $filename = $data["location"];
        if ($filename && is_null(parse_url($filename, PHP_URL_SCHEME))) {
            try {
                Storage::downloads()->deleteAllowNotPresent($filename);
            } catch (Exception $e) {
                $aInt->gracefulExit("Could not delete file: " . htmlentities($e->getMessage()));
            }
        }
        delete_query("tbldownloads", array("id" => $id));
        logActivity("Deleted Download (ID: " . $id . ")");
        redir("catid=" . $catid);
    }
    if ($sub == "deletecategory") {
        check_token("WHMCS.admin.default");
        delete_query("tbldownloads", array("category" => $id));
        delete_query("tbldownloadcats", array("id" => $id));
        logActivity("Deleted Download Category (ID: " . $id . ")");
        redir("catid=" . $catid);
    }
    $breadcrumbnav = "";
    if ($catid != "0") {
        $result = select_query("tbldownloadcats", "", array("id" => $catid));
        $data = mysql_fetch_array($result);
        $catid = $data["id"];
        if (!$catid) {
            $aInt->gracefulExit("Category ID Not Found");
        }
        $catparentid = $data["parentid"];
        $catname = $data["name"];
        $catbreadcrumbnav = " > <a href=\"supportdownloads.php?catid=" . $catid . "\">" . $catname . "</a>";
        while ($catparentid != "0") {
            $result = select_query("tbldownloadcats", "", array("id" => $catparentid));
            $data = mysql_fetch_array($result);
            $cattempid = $data["id"];
            $catparentid = $data["parentid"];
            $catname = $data["name"];
            $catbreadcrumbnav = " > <a href=\"supportdownloads.php?catid=" . $cattempid . "\">" . $catname . "</a>" . $catbreadcrumbnav;
        }
        $breadcrumbnav .= $catbreadcrumbnav;
    }
    $aInt->deleteJSConfirm("doDelete", "support", "dldelsure", $_SERVER["PHP_SELF"] . "?catid=" . $catid . "&sub=delete&id=");
    $aInt->deleteJSConfirm("doDeleteCat", "support", "dlcatdelsure", $_SERVER["PHP_SELF"] . "?catid=" . $catid . "&sub=deletecategory&id=");
    echo $aInt->beginAdminTabs(array($aInt->lang("support", "addcategory"), $aInt->lang("support", "adddownload")), $defaultTabOpen);
    echo "\n    <form method=\"post\" action=\"";
    echo $whmcs->getPhpSelf();
    echo "?catid=";
    echo $catid;
    echo "&addcategory=true\">\n        <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n            <tr>\n                <td width=\"15%\" class=\"fieldlabel\">";
    echo $aInt->lang("support", "catname");
    echo "</td>\n                <td class=\"fieldarea\">\n                    <input type=\"text\" name=\"catname\" class=\"form-control input-inline input-300\">\n                    <label class=\"checkbox-inline\">\n                        <input type=\"checkbox\" name=\"hidden\" />\n                        ";
    echo AdminLang::trans("support.ticktohide");
    echo "                    </label>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">\n                    ";
    echo $aInt->lang("fields", "description");
    echo "                </td>\n                <td class=\"fieldarea\">\n                    <input type=\"text\" name=\"description\" class=\"form-control input-600\">\n                </td>\n            </tr>\n        </table>\n\n        <div class=\"btn-container\">\n            <button id=\"btnAddCategory\" class=\"btn btn-primary\" type=\"submit\">\n                ";
    echo AdminLang::trans("support.addcategory");
    echo "            </button>\n        </div>\n    </form>\n\n    ";
    echo $aInt->nextAdminTab();
    echo "\n    ";
    if ($catid != "") {
        if ($remoteDownload) {
            infoBox(AdminLang::trans("support.invalidFilename"), AdminLang::trans("support.invalidFilenameDownloadDescription"), "error");
            echo $infobox;
        }
        echo "        <form method=\"post\" action=\"";
        echo $whmcs->getPhpSelf();
        echo "?catid=";
        echo $catid;
        echo "&adddownload=true\" name=\"sample\" enctype=\"multipart/form-data\">\n            <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n                <tr>\n                    <td width=\"15%\" class=\"fieldlabel\">\n                        ";
        echo $aInt->lang("fields", "type");
        echo "                    </td>\n                    <td class=\"fieldarea\">\n                        <select name=\"type\" class=\"form-control select-inline\">\n                            <option value=\"zip\">\n                                ";
        echo $aInt->lang("support", "zipfile");
        echo "                            </option>\n                            <option value=\"exe\">\n                                ";
        echo $aInt->lang("support", "exefile");
        echo "                            </option>\n                            <option value=\"pdf\">\n                                ";
        echo $aInt->lang("support", "pdffile");
        echo "                            </option>\n                        </select>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">";
        echo $aInt->lang("fields", "title");
        echo "</td>\n                    <td class=\"fieldarea\">\n                        <input type=\"text\" name=\"title\" class=\"form-control input-400\">\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">";
        echo $aInt->lang("fields", "description");
        echo "</td>\n                    <td class=\"fieldarea\">\n                        <textarea name=\"description\" class=\"form-control\" rows=\"3\"></textarea>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "uploadfile");
        echo "</td>\n                    <td class=\"fieldarea\">\n                        <label class=\"radio-inline\">\n                            <input type=\"radio\" name=\"filetype\" value=\"manual\" checked> ";
        echo $aInt->lang("support", "manualftp");
        echo "                        </label>\n                        <br />\n                        ";
        echo $aInt->lang("support", "enterfilename");
        echo ": <input type=\"text\" name=\"filename\" class=\"form-control input-inline input-400\">\n                        <br />\n                        <label class=\"radio-inline\">\n                            <input type=\"radio\" name=\"filetype\" value=\"upload\"> ";
        echo $aInt->lang("support", "uploadfile");
        echo "                        </label>\n                        <br />\n                        ";
        echo $aInt->lang("support", "choosefile");
        echo ": <input type=\"file\" name=\"uploadfile\" style=\"width:80%\">\n                        <br />\n                        ";
        echo "<font style=\"color:#cc0000\">" . $aInt->lang("support", "servermaxfile") . ": <strong>" . ini_get("upload_max_filesize") . "</strong> - " . $aInt->lang("support", "howtoincrease");
        echo "                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "clientsonly");
        echo "</td>\n                    <td class=\"fieldarea\">\n                        <label class=\"checkbox-inline\">\n                            <input type=\"checkbox\" name=\"clientsonly\"> ";
        echo $aInt->lang("support", "clientsonlyinfo");
        echo "                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "productdl");
        echo "</td>\n                    <td class=\"fieldarea\">\n                        <label class=\"checkbox-inline\">\n                            <input type=\"checkbox\" name=\"productdownload\"> ";
        echo $aInt->lang("support", "productdlinfo");
        echo "                        </label>\n                    </td>\n                </tr>\n                <tr>\n                    <td class=\"fieldlabel\">";
        echo $aInt->lang("global", "hidden");
        echo "</td>\n                    <td class=\"fieldarea\">\n                        <label class=\"checkbox-inline\">\n                            <input type=\"checkbox\" name=\"hidden\"> ";
        echo $aInt->lang("support", "hiddeninfo");
        echo "                        </label>\n                    </td>\n                </tr>\n            </table>\n            <div class=\"btn-container\">\n                <input type=\"submit\" value=\"";
        echo $aInt->lang("support", "adddownload");
        echo "\" class=\"btn btn-primary\" />\n                <input type=\"button\" value=\"";
        echo $aInt->lang("global", "cancelchanges");
        echo "\" onClick=\"window.location='";
        echo $whmcs->getPhpSelf();
        echo "'\" class=\"btn btn-default\" />\n            </div>\n        </form>\n    ";
    } else {
        echo "        <div class=\"alert alert-info bottom-margin-5\">\n            ";
        echo AdminLang::trans("support.notoplevel");
        echo "        </div>\n    ";
    }
    echo $aInt->endAdminTabs();
    echo "<br><p>" . $aInt->lang("support", "youarehere") . ": <a href=\"" . $whmcs->getPhpSelf() . "\">" . $aInt->lang("support", "dlhome") . "</a> " . $breadcrumbnav . "</p>";
    $result = select_query("tbldownloadcats", "", array("parentid" => $catid), "name", "ASC");
    $numcats = mysql_num_rows($result);
    $editImage = "<img src='images/edit.gif' align='absmiddle' border='0' alt='" . $aInt->lang("global", "edit") . "' />";
    $deleteImage = "<img src='images/delete.gif' align='absmiddle' border='0' alt='" . $aInt->lang("global", "delete") . "' />";
    $folderImage = WHMCS\View\Asset::imgTag("folder.gif", $aInt->lang("support", "category"));
    echo "\n    ";
    if ($numcats != "0") {
        echo "        <div class=\"browse-section-title\">\n            ";
        echo $aInt->lang("support", "categories");
        echo "        </div>\n        <div class=\"row\">\n            ";
        if ($catid == "") {
            $catid = "0";
        }
        $result = select_query("tbldownloadcats", "", array("parentid" => $catid), "name", "ASC");
        $i = 0;
        while ($data = mysql_fetch_array($result)) {
            $id = $data["id"];
            $name = $data["name"];
            $description = $data["description"];
            $hidden = $data["hidden"];
            $idnumbers = array($id);
            $newIdNumbers = WHMCS\Database\Capsule::table("tbldownloadcats")->where("parentid", $id)->pluck("id");
            while (0 < count($newIdNumbers)) {
                $idnumbers = array_merge($idnumbers, $newIdNumbers);
                $newIdNumbers = WHMCS\Database\Capsule::table("tbldownloadcats")->whereIn("parentid", $newIdNumbers)->pluck("id");
            }
            $numarticles = WHMCS\Database\Capsule::table("tbldownloads")->whereIn("category", $idnumbers)->count();
            $hiddenOutput = "";
            if ($hidden) {
                $text = strtoupper(AdminLang::trans("fields.hidden"));
                $hiddenOutput = "<span class=\"grey-item\">" . $text . "</span>";
            }
            echo "<div class=\"col-md-4 col-sm-6\">\n    " . $folderImage . " <a href=\"supportdownloads.php?catid=" . $id . "\"><b>" . $name . "</b></a> (" . $numarticles . ")\n    <a href=\"supportdownloads.php?action=editcat&id=" . $id . "\">" . $editImage . "</a>\n    <a href=\"#\" onClick=\"doDeleteCat(" . $id . "); return false;\">" . $deleteImage . "</a>\n    " . $hiddenOutput . "\n    <br>" . $description . "\n</div>";
        }
        echo "</div>";
    }
    $result = select_query("tbldownloads", "", array("category" => $catid), "title", "ASC");
    $numarticles = mysql_num_rows($result);
    if ($numarticles != "0") {
        echo "        <div class=\"browse-section-title\">\n            ";
        echo AdminLang::trans("clientsummary.filesheading");
        echo "        </div>\n\n        <div class=\"row\">\n            ";
        $result = select_query("tbldownloads", "", array("category" => $catid), "title", "ASC");
        while ($data = mysql_fetch_array($result)) {
            $id = $data["id"];
            $category = $data["category"];
            $title = $data["title"];
            $description = strip_tags($data["description"]);
            $downloads = $data["downloads"];
            $clientsonly = $data["clientsonly"];
            $hidden = $data["hidden"];
            $downloadImage = WHMCS\View\Asset::imgTag("article.gif", AdminLang::trans("support.download"));
            $clientOnlyOutput = $hiddenOutput = "";
            if ($clientsonly) {
                $clientOnlyOutput = "<span class=\"grey-item\">" . strtoupper(AdminLang::trans("support.clientsonly")) . "</span>";
            }
            if ($hidden) {
                $hiddenOutput = "<span class=\"grey-item\">" . strtoupper(AdminLang::trans("fields.hidden")) . "</span>";
            }
            $downloadsText = AdminLang::trans("support.downloads");
            echo "<div class=\"col-sm-8\">\n    " . $downloadImage . " <a href=\"supportdownloads.php?action=edit&id=" . $id . "\"><b>" . $title . "</b></a>\n    <a href=\"#\" onClick=\"doDelete('" . $id . "'); return false;\">" . $deleteImage . "</a>\n    " . $clientOnlyOutput . "\n    " . $hiddenOutput . "\n    <br>" . $description . "\n    <br>\n    <span class=\"grey-item\">\n        " . $downloadsText . ": " . $downloads . "\n    </span>\n</div>";
        }
        echo "        </div>\n    ";
    } else {
        if ($catid) {
            echo "        <div class=\"margin-top-bottom-20\">\n            <strong>\n                ";
            echo AdminLang::trans("support.nodlfiles");
            echo "            </strong>\n        </div>\n    ";
        }
    }
} else {
    if ($action == "edit") {
        $result = select_query("tbldownloads", "", array("id" => $id));
        $data = mysql_fetch_array($result);
        $category = $data["category"];
        $type = $data["type"];
        $title = $data["title"];
        $description = $data["description"];
        $downloads = $data["downloads"];
        $location = $data["location"];
        $hidden = (int) (bool) $data["hidden"];
        $clientsonly = (int) (bool) $data["clientsonly"];
        $productdownload = (int) (bool) $data["productdownload"];
        if ($remoteDownload) {
            infoBox(AdminLang::trans("support.invalidFilename"), AdminLang::trans("support.invalidFilenameDownloadDescription"), "error");
            echo $infobox;
        }
        echo "\n    <form method=\"post\" action=\"";
        echo $whmcs->getPhpSelf();
        echo "?catid=";
        echo $category;
        echo "&sub=save&id=";
        echo $id;
        echo "\">\n        <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n            <tr>\n                <td width=\"15%\" class=\"fieldlabel\">";
        echo $aInt->lang("support", "category");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <select name=\"category\" class=\"form-control select-inline\">\n                        ";
        $result = select_query("tbldownloadcats", "", "", "parentid` ASC,`name", "ASC");
        while ($data = mysql_fetch_array($result)) {
            $catid = $data["id"];
            $category2 = $data["name"];
            echo "<option value=\"" . $catid . "\"";
            if ($catid == $category) {
                echo " selected";
            }
            echo ">" . $category2;
        }
        echo "                    </select>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("fields", "type");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <select name=\"type\" class=\"form-control select-inline\">\n                        <option value=\"zip\" ";
        if ($type == "zip") {
            echo "SELECTED";
        }
        echo ">";
        echo $aInt->lang("support", "zipfile");
        echo "</option>\n                        <option value=\"exe\" ";
        if ($type == "exe") {
            echo "SELECTED";
        }
        echo ">";
        echo $aInt->lang("support", "exefile");
        echo "</option>\n                        <option value=\"pdf\" ";
        if ($type == "pdf") {
            echo "SELECTED";
        }
        echo ">";
        echo $aInt->lang("support", "pdffile");
        echo "</option>\n                    </select>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("fields", "title");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <input type=\"text\" name=\"title\" value=\"";
        echo $title;
        echo "\" class=\"form-control input-400\">\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("fields", "description");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <textarea name=\"description\" rows=3 class=\"form-control\">\n";
        echo $description;
        echo "</textarea>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "filename");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <input type=\"text\" name=\"location\" value=\"";
        echo $location;
        echo "\" class=\"form-control input-600\">\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "downloads");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <input type=\"number\" name=\"downloads\" value=\"";
        echo $downloads;
        echo "\" class=\"form-control input-75\">\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "clientsonly");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <label class=\"checkbox-inline\">\n                        <input type=\"checkbox\" name=\"clientsonly\"";
        if ($clientsonly) {
            echo "checked";
        }
        echo "> ";
        echo $aInt->lang("support", "clientsonlyinfo");
        echo "                    </label>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "productdl");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <label class=\"checkbox-inline\">\n                        <input type=\"checkbox\" name=\"productdownload\"";
        if ($productdownload) {
            echo "checked";
        }
        echo "> ";
        echo $aInt->lang("support", "productdlinfo");
        echo "                    </label>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("global", "hidden");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <label class=\"checkbox-inline\">\n                        <input type=\"checkbox\" name=\"hidden\"";
        if ($hidden) {
            echo "checked";
        }
        echo " /> ";
        echo $aInt->lang("support", "hiddeninfo");
        echo "                    </label>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
        echo $aInt->lang("support", "downloadlink");
        echo "</td>\n                <td class=\"fieldarea\">\n                    <input type=\"text\"\n                           class=\"form-control input-700\"\n                           value=\"";
        echo App::getSystemURL();
        echo "dl.php?type=d&id=";
        echo $id;
        echo "\"\n                           readonly\n                    >\n                </td>\n            </tr>\n        </table>\n\n        <div class=\"btn-container\">\n            <input type=\"submit\" value=\"";
        echo $aInt->lang("global", "savechanges");
        echo "\" class=\"btn btn-primary\"> <input type=\"button\" value=\"";
        echo $aInt->lang("global", "cancelchanges");
        echo "\" class=\"btn btn-default\" onclick=\"history.go(-1)\" />\n        </div>\n    </form>\n";
    } else {
        if ($action == "editcat") {
            $result = select_query("tbldownloadcats", "", array("id" => $id));
            $data = mysql_fetch_array($result);
            $parentid = $data["parentid"];
            $name = $data["name"];
            $description = $data["description"];
            $hidden = (int) (bool) $data["hidden"];
            echo "    <form method=\"post\" action=\"";
            echo $whmcs->getPhpSelf();
            echo "?catid=";
            echo $parentid;
            echo "&sub=savecat&id=";
            echo $id;
            echo "\">\n        <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n            <tr>\n                <td width=\"15%\" class=\"fieldlabel\">";
            echo $aInt->lang("support", "parentcat");
            echo "</td>\n                <td class=\"fieldarea\">\n                    <select name=\"parentcategory\" class=\"form-control select-inline\">\n                        <option value=\"\">";
            echo $aInt->lang("support", "toplevel");
            echo "                        ";
            $result = select_query("tbldownloadcats", "", "", "parentid` ASC,`name", "ASC");
            while ($data = mysql_fetch_array($result)) {
                $id = $data["id"];
                $category2 = $data["name"];
                echo "<option value=\"" . $id . "\"";
                if ($id == $parentid) {
                    echo " selected";
                }
                echo ">" . $category2;
            }
            echo "                    </select>\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
            echo $aInt->lang("support", "catname");
            echo "</td>\n                <td class=\"fieldarea\">\n                    <input type=\"text\" name=\"name\" value=\"";
            echo $name;
            echo "\" class=\"form-control input-300\">\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
            echo $aInt->lang("fields", "description");
            echo "</td>\n                <td class=\"fieldarea\">\n                    <input type=\"text\" name=\"description\" value=\"";
            echo $description;
            echo "\" class=\"form-control input-700\">\n                </td>\n            </tr>\n            <tr>\n                <td class=\"fieldlabel\">";
            echo $aInt->lang("fields", "hidden");
            echo "</td>\n                <td class=\"fieldarea\">\n                    <label class=\"checkbox-inline\">\n                        <input type=\"checkbox\" name=\"hidden\"";
            echo $hidden ? " checked" : "";
            echo ">\n                        ";
            echo $aInt->lang("support", "hiddeninfo");
            echo "                    </label>\n                </td>\n            </tr>\n        </table>\n\n        <div class=\"btn-container\">\n            <input type=\"submit\" value=\"";
            echo $aInt->lang("global", "savechanges");
            echo "\" class=\"btn btn-primary\"> <input type=\"button\" value=\"";
            echo $aInt->lang("global", "cancelchanges");
            echo "\" class=\"btn btn-default\" onclick=\"history.go(-1)\" />\n        </div>\n    </form>\n";
        }
    }
}
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();

?>