<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Service;

class ServiceController
{
    protected function listServices($serviceType = "")
    {
        $aInt = new \WHMCS\Admin("List Services");
        $aInt->setResponseType(\WHMCS\Admin::RESPONSE_HTML_MESSAGE);
        switch ($serviceType) {
            case "hostingaccount":
                $pageTitle = \AdminLang::trans("services.listhosting");
                $path = "admin-services-shared";
                break;
            case "reselleraccount":
                $pageTitle = \AdminLang::trans("services.listreseller");
                $path = "admin-services-reseller";
                break;
            case "server":
                $pageTitle = \AdminLang::trans("services.listservers");
                $path = "admin-services-server";
                break;
            case "other":
                $pageTitle = \AdminLang::trans("services.listother");
                $path = "admin-services-other";
                break;
            default:
                $pageTitle = \AdminLang::trans("services.title");
                $path = "admin-services-index";
        }
        $aInt->title = $pageTitle;
        $aInt->sidebar = "clients";
        $aInt->icon = "products";
        $aInt->requiredFiles(array("clientfunctions", "customfieldfunctions", "gatewayfunctions"));
        $name = "services";
        $orderby = "id";
        $sort = "DESC";
        $pageObj = new \WHMCS\Pagination($name, $orderby, $sort);
        $pageObj->digestCookieData();
        $tbl = new \WHMCS\ListTable($pageObj, 0, $aInt);
        $tbl->setColumns(array("checkall", array("id", \AdminLang::trans("fields.id")), array("product", \AdminLang::trans("fields.product")), array("domain", \AdminLang::trans("fields.domain")), array("clientname", \AdminLang::trans("fields.clientname")), array("amount", \AdminLang::trans("fields.price")), array("billingcycle", \AdminLang::trans("fields.billingcycle")), array("nextduedate", \AdminLang::trans("fields.nextduedate")), array("domainstatus", \AdminLang::trans("fields.status")), ""));
        $serviceData = new Table\Service($pageObj);
        $filter = (new \WHMCS\Filter("admin-services-index"))->setAllowedVars(array("clientname", "type", "package", "productname", "billingcycle", "server", "paymentmethod", "nextduedate", "status", "domain", "username", "dedicatedip", "assignedips", "package", "id", "subscriptionid", "notes", "customfieldvalue", "customfield"));
        $searchCriteria = $filter->store()->getFilterCriteria();
        if (!$searchCriteria["type"] && $serviceType) {
            $searchCriteria["type"] = $serviceType;
        }
        $serviceData->execute($searchCriteria);
        $serviceList = $pageObj->getData();
        foreach ($serviceList as $data) {
            $id = $data["id"];
            $userId = $data["userid"];
            $domain = $data["domain"];
            $dType = $data["type"];
            $dPackage = $data["name"];
            $firstPaymentAmount = $data["firstpaymentamount"];
            $amount = $data["amount"];
            $billingCycle = $data["billingcycle"];
            $nextDueDate = $data["nextduedate"];
            $status = $data["domainstatus"];
            $firstName = $data["firstname"];
            $lastName = $data["lastname"];
            $companyName = $data["companyname"];
            $groupId = $data["groupid"];
            $currency = $data["currency"];
            if (!$domain) {
                $domain = "(" . \AdminLang::trans("addons.nodomain") . ")";
            }
            $linkValue = "";
            if ($dType != "other") {
                $style = "color:#cc0000";
                $linkValue = " <a href=\"http://" . $domain . "\" target=\"_blank\" style=\"" . $style . ";\">" . "<small>www</small></a>";
            }
            if ($billingCycle == "One Time" || $billingCycle == "Free Account") {
                $nextDueDate = "0000-00-00";
                $amount = $firstPaymentAmount;
            }
            $amount = formatCurrency($amount, $currency);
            $nextDueDate = $nextDueDate == "0000-00-00" ? "-" : fromMySQLDate($nextDueDate);
            $langVar = str_replace(array("-", "account", " "), "", strtolower($billingCycle));
            $billingCycle = \AdminLang::trans("billingcycles." . $langVar);
            $checkbox = "<input type=\"checkbox\" name=\"selectedclients[]\"" . " value=\"" . $id . "\" class=\"checkall\" />";
            $hostingUri = "clientsservices.php?userid=" . $userId . "&id=" . $id;
            $hostingLink = "<a href=\"" . $hostingUri . "\">" . $domain . "</a>";
            $hostingIdLink = "<a href=\"" . $hostingUri . "\">" . $id . "</a>";
            $statusBadge = "<span class=\"label " . strtolower($status) . "\">" . $status . "</span>";
            $expandIcon = "<a href=\"" . routePath("admin-services-detail", $id) . "\" class=\"view-detail\"><i class=\"fa fa-plus\"></i></a>";
            $tbl->addRow(array($checkbox, $hostingIdLink, $dPackage, $hostingLink . $linkValue, $aInt->outputClientLink($userId, $firstName, $lastName, $companyName, $groupId), $amount, $billingCycle, $nextDueDate, $statusBadge, $expandIcon));
        }
        $tbl->setMassActionURL("sendmessage.php?type=product&multiple=true");
        $tbl->setMassActionBtns("<button type=\"submit\" class=\"btn btn-default\">" . \AdminLang::trans("global.sendmessage") . "</button>");
        $pageObj->setBasePath(routePath($path));
        $tbl->setShowHidden(\App::getFromRequest("show_hidden"));
        $tableOutput = $tbl->output();
        unset($serviceData);
        unset($serviceList);
        $serverData = \WHMCS\View\Helper::getServerDropdownOptions($searchCriteria["server"]);
        $servers = $serverData["servers"];
        $disabledServers = $serverData["disabledServers"];
        $aInt->content = view("admin.client.services.index", array("criteria" => $searchCriteria, "tableOutput" => $tableOutput, "products" => $aInt->productDropDown((int) $searchCriteria["package"], false, true), "paymentMethods" => paymentMethodsSelection(\AdminLang::trans("global.any")), "cycles" => $aInt->cyclesDropDown($searchCriteria["billingcycle"], true), "servers" => $servers . $disabledServers, "statuses" => $aInt->productStatusDropDown($searchCriteria["status"], true), "customFields" => \WHMCS\CustomField::where("type", "product")->get(), "tabStart" => $aInt->beginAdminTabs(array(\AdminLang::trans("global.searchfilter"))), "tabEnd" => $aInt->endAdminTabs()));
        return $aInt->display();
    }
    public function index(\WHMCS\Http\Message\ServerRequest $request)
    {
        return $this->listServices();
    }
    public function shared(\WHMCS\Http\Message\ServerRequest $request)
    {
        return $this->listServices("hostingaccount");
    }
    public function reseller(\WHMCS\Http\Message\ServerRequest $request)
    {
        return $this->listServices("reselleraccount");
    }
    public function server(\WHMCS\Http\Message\ServerRequest $request)
    {
        return $this->listServices("server");
    }
    public function other(\WHMCS\Http\Message\ServerRequest $request)
    {
        return $this->listServices("other");
    }
    public function serviceDetail(\WHMCS\Http\Message\ServerRequest $request)
    {
        $service = \WHMCS\Service\Service::with("order", "serverModel", "paymentGateway", "promotion")->findOrFail($request->attributes()->get("serviceid"));
        $data = array(array(\AdminLang::trans("fields.ordernum") => $service->orderId ? $service->order->orderNumber : "", \AdminLang::trans("fields.regdate") => $service->registrationDate->toAdminDateFormat()), array(\AdminLang::trans("fields.server") => $service->serverId ? $service->serverModel->name : "", \AdminLang::trans("fields.dedicatedip") => $service->dedicatedIp ? $service->dedicatedIp : "", \AdminLang::trans("fields.username") => $service->username), array(\AdminLang::trans("fields.paymentmethod") => $service->paymentGateway()->name()->first()->value, \AdminLang::trans("fields.promocode") => $service->promotionId ? $service->promotion()->first()->code : ""));
        return new \WHMCS\Http\Message\JsonResponse(array("output" => \WHMCS\View\Bootstrap::renderKeyValuePairsInColumns(4, $data)));
    }
    public function subscriptionInfo(\WHMCS\Http\Message\ServerRequest $request)
    {
        $relatedId = $request->get("id");
        try {
            $relatedItem = \WHMCS\Service\Service::findOrFail($relatedId);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid Access Attempt");
        }
        return \WHMCS\Payment\Subscription::getInfo($relatedItem);
    }
    public function subscriptionCancel(\WHMCS\Http\Message\ServerRequest $request)
    {
        $relatedId = $request->get("id");
        try {
            $relatedItem = \WHMCS\Service\Service::findOrFail($relatedId);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid Access Attempt");
        }
        return \WHMCS\Payment\Subscription::cancel($relatedItem);
    }
}

?>