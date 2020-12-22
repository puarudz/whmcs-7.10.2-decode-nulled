<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Utilities\System\Automation;

class AutomationController
{
    public function getDetail(\WHMCS\Http\Message\ServerRequest $request)
    {
        $date = \WHMCS\Carbon::createFromFormat("Y-m-d", $request->get("date"));
        $namespaceId = $request->get("namespaceId");
        $tab = $request->get("tab", 1);
        $logs = \WHMCS\Log\Register::onDateByNamespaceId($date, $namespaceId)->actionDetails()->get();
        $errorsOutput = false;
        $namespace = "unknown";
        $tabTitles = array();
        $tabs = array();
        foreach ($logs as $log) {
            $namespaceParts = explode(".", $log->namespace, 2);
            if ($namespace === "unknown") {
                $namespace = lcfirst($namespaceParts[0]);
            }
            if (!$log->namespace_value) {
                continue;
            }
            $details = json_decode($log->namespace_value, true);
            foreach ($details as $tabTitle => $detail) {
                if (!in_array($tabTitle, $tabTitles)) {
                    $tabTitles[] = $tabTitle;
                }
                if (!array_key_exists($tabTitle, $tabs)) {
                    $tabs[$tabTitle] = array();
                }
                $detail = array_filter($detail);
                foreach ($detail as $data) {
                    list($type, $id) = $data;
                    $errorMessage = "";
                    switch ($type) {
                        case "addon":
                            $item = \WHMCS\Service\Addon::with("client", "failedActions")->find($id);
                            break;
                        case "client":
                            $item = \WHMCS\User\Client::find($id);
                            break;
                        case "domain":
                            $item = \WHMCS\Domain\Domain::with("client")->find($id);
                            break;
                        case "invoice":
                            $item = \WHMCS\Billing\Invoice::with("client", "transactionHistory")->find($id);
                            break;
                        case "service":
                            $item = \WHMCS\Service\Service::with("client", "failedActions", "product", "product.productGroup")->find($id);
                            break;
                        case "ticket":
                            $item = \WHMCS\Support\Ticket::with("client")->find($id);
                            break;
                        default:
                            $item = null;
                    }
                    if (is_null($item)) {
                        continue;
                    }
                    if (count($data) == 3) {
                        $errorMessage = $data[2];
                        if ($errorMessage) {
                            $errorsOutput = true;
                        }
                        if ($item instanceof \WHMCS\Billing\Invoice && $tabTitle == "failure" && $log->namespace == "ProcessCreditCardPayments.action.detail") {
                            $history = $item->transactionHistory->first();
                            $errorMessage = "Payment Declined";
                            if ($history) {
                                $errorMessage = $history->description;
                            }
                            $errorsOutput = true;
                        }
                    }
                    $tabs[$tabTitle][] = array("item" => $item, "error" => $errorMessage);
                }
            }
        }
        $clientNamespaces = array("addLateFees", "autoClientStatusSync", "createInvoices", "domainRenewalNotices", "invoiceReminders", "processCreditCardPayments");
        $invoiceNamespaces = array("addLateFees", "createInvoices", "invoiceReminders", "processCreditCardPayments");
        $domainNamespaces = array("domainRenewalNotices");
        $idTitle = "fields.serviceAddonId";
        $statusTitle = "fields.serviceAddonStatus";
        if (in_array($namespace, $invoiceNamespaces)) {
            $idTitle = "fields.invoiceid";
            $statusTitle = "fields.invoiceStatus";
        } else {
            if (in_array($namespace, $domainNamespaces)) {
                $idTitle = "fields.domainId";
                $statusTitle = "fields.domainStatus";
            } else {
                if ($namespace == "autoClientStatusSync") {
                    $idTitle = "fields.clientid";
                    $statusTitle = "fields.clientStatus";
                }
            }
        }
        return new \WHMCS\Http\Message\JsonResponse(array("body" => view("admin.utilities.system.automation.detail-modal", array("selectedTab" => $tab, "tabTitles" => $tabTitles, "tabContent" => $tabs, "namespace" => $namespace, "isClient" => in_array($namespace, $clientNamespaces), "isInvoice" => in_array($namespace, $invoiceNamespaces), "isDomain" => in_array($namespace, $domainNamespaces), "errorsOutput" => $errorsOutput, "idTitle" => $idTitle, "statusTitle" => $statusTitle, "queueUri" => \App::getSystemURL() . \App::get_admin_folder_name() . DIRECTORY_SEPARATOR . "modulequeue.php"))));
    }
}

?>