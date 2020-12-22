<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Cron\Task;

class AutoClientStatusSync extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1680;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Synchronise Client Status";
    protected $defaultName = "Client Status Update";
    protected $systemName = "AutoClientStatusSync";
    protected $outputs = array("active.product.domain" => array("defaultValue" => 0, "identifier" => "active.product.domain", "name" => "Active due to domain"), "active.product.addon" => array("defaultValue" => 0, "identifier" => "active.product.addon", "name" => "Active due to addon"), "active.product.service" => array("defaultValue" => 0, "identifier" => "active.product.service", "name" => "Active due to service"), "active.billable.item" => array("defaultValue" => 0, "identifier" => "active.billable.item", "name" => "Active due to billable item"), "inactive.login" => array("defaultValue" => 0, "identifier" => "inactive.login", "name" => "Inactive due to no login"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"), "processed" => array("defaultValue" => 0, "identifier" => "processed", "name" => "Client Status Synced"));
    protected $icon = "fas fa-sort";
    protected $successCountIdentifier = "processed";
    protected $successKeyword = "Completed";
    private $activeClients = array();
    private $inactiveClients = array();
    protected $hasDetail = true;
    public function __invoke()
    {
        if (\WHMCS\Config\Setting::getValue("AutoClientStatusChange") == "1") {
            $this->output("processed")->write(0);
            $this->output("active.product.addon")->write(0);
            $this->output("active.product.domain")->write(0);
            $this->output("active.product.service")->write(0);
            $this->output("inactive.login")->write(0);
            $this->output("success.detail")->write("{}");
            $this->output("failure.detail")->write("{}");
            return $this;
        }
        $this->deactivateClientsWithoutLoginActivity()->activateClientsWithActiveHostingProduct()->activateClientsWithActiveProductAddon()->activateClientsWithActiveDomainProduct()->activateClientsWithActiveBillableItems();
        foreach ($this->activeClients as $client) {
            $this->addSuccess(array("client", $client));
        }
        foreach ($this->inactiveClients as $client) {
            $this->addFailure(array("client", $client));
        }
        $this->output("action.detail")->write(json_encode($this->getDetail()));
        $this->output("processed")->write(count($this->activeClients) + count($this->inactiveClients));
        return $this;
    }
    protected function activateClientsWithActiveDomainProduct()
    {
        $clientIds = \WHMCS\Database\Capsule::table("tbldomains")->join("tblclients", "tblclients.id", "=", "tbldomains.userid")->where("tblclients.status", \WHMCS\User\Client::STATUS_INACTIVE)->where("tblclients.overrideautoclose", "0")->whereIn("tbldomains.status", array(\WHMCS\Domain\Status::ACTIVE, \WHMCS\Domain\Status::PENDING_TRANSFER))->pluck("tbldomains.userid");
        if (count($clientIds)) {
            \WHMCS\Database\Capsule::table("tblclients")->whereIn("id", $clientIds)->update(array("status" => \WHMCS\User\Client::STATUS_ACTIVE));
        }
        $this->output("active.product.domain")->write(count($clientIds));
        $this->activeClients = array_merge($this->activeClients, $clientIds);
        return $this;
    }
    protected function activateClientsWithActiveProductAddon()
    {
        $clientIds = \WHMCS\Database\Capsule::table("tblhostingaddons")->join("tblhosting", "tblhosting.id", "=", "tblhostingaddons.hostingid")->join("tblclients", "tblclients.id", "=", "tblhosting.id")->where("tblclients.status", \WHMCS\User\Client::STATUS_INACTIVE)->where("tblclients.overrideautoclose", "0")->whereIn("tblhostingaddons.status", array(\WHMCS\Service\Status::ACTIVE, \WHMCS\Service\Status::SUSPENDED))->pluck("tblhosting.userid");
        if (count($clientIds)) {
            \WHMCS\Database\Capsule::table("tblclients")->whereIn("id", $clientIds)->update(array("status" => \WHMCS\User\Client::STATUS_ACTIVE));
        }
        $this->output("active.product.addon")->write(count($clientIds));
        $this->activeClients = array_merge($this->activeClients, $clientIds);
        return $this;
    }
    protected function activateClientsWithActiveHostingProduct()
    {
        $clientIds = \WHMCS\Database\Capsule::table("tblhosting")->join("tblclients", "tblclients.id", "=", "tblhosting.userid")->where("tblclients.status", \WHMCS\User\Client::STATUS_INACTIVE)->where("tblclients.overrideautoclose", "0")->whereIn("tblhosting.domainstatus", array(\WHMCS\Service\Status::ACTIVE, \WHMCS\Service\Status::SUSPENDED))->pluck("tblhosting.userid");
        if (count($clientIds)) {
            \WHMCS\Database\Capsule::table("tblclients")->whereIn("id", $clientIds)->update(array("status" => \WHMCS\User\Client::STATUS_ACTIVE));
        }
        $this->output("active.product.service")->write(count($clientIds));
        $this->activeClients = array_merge($this->activeClients, $clientIds);
        return $this;
    }
    protected function activateClientsWithActiveBillableItems()
    {
        $ids = \WHMCS\Database\Capsule::table("tblbillableitems")->join("tblclients", "tblclients.id", "=", "tblbillableitems.userid")->where("tblclients.status", "=", "Inactive")->where("tblclients.overrideautoclose", "=", 0)->where("tblbillableitems.invoiceaction", "=", 4)->where("tblbillableitems.recurfor", ">", "tblbillableitems.invoicecount")->pluck("tblclients.id");
        \WHMCS\Database\Capsule::table("tblclients")->whereIn("id", $ids)->update(array("status" => \WHMCS\User\Client::STATUS_ACTIVE));
        $this->output("active.billable.item")->write(count($ids));
        $this->activeClients = array_merge($this->activeClients, $ids);
        return $this;
    }
    protected function deactivateClientsWithoutLoginActivity()
    {
        $clientsModified = array();
        $query = "SELECT id,lastlogin FROM tblclients" . " WHERE status='Active'" . " AND overrideautoclose='0'" . " AND (" . "SELECT COUNT(id) FROM tblhosting" . " WHERE tblhosting.userid=tblclients.id" . " AND domainstatus IN ('Active','Suspended')" . ")=0";
        if (\WHMCS\Config\Setting::getValue("AutoClientStatusChange") == "3") {
            $query .= sprintf(" AND lastlogin<='%s'", date("Y-m-d", mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"))));
        }
        $result = full_query($query);
        while ($data = mysql_fetch_array($result)) {
            $userid = $data["id"];
            $result2 = full_query("SELECT (" . "SELECT COUNT(*) FROM tblhosting" . " WHERE userid=tblclients.id" . " AND domainstatus IN ('Active','Suspended')" . ")+(" . "SELECT COUNT(*) FROM tblhostingaddons" . " WHERE hostingid IN (" . "SELECT id FROM tblhosting" . " WHERE userid=tblclients.id)" . " AND status IN ('Active','Suspended')" . ")+(" . "SELECT COUNT(*) FROM tbldomains" . " WHERE userid=tblclients.id" . " AND status IN ('Active')" . ") AS activeservices FROM tblclients" . " WHERE tblclients.id=" . (int) $userid . " LIMIT 1");
            $data = mysql_fetch_array($result2);
            $totalactivecount = $data[0];
            if ($totalactivecount == 0) {
                update_query("tblclients", array("status" => "Inactive"), array("id" => $userid));
                $clientsModified[] = $userid;
            }
        }
        $this->output("inactive.login")->write(count($clientsModified));
        $this->inactiveClients = $clientsModified;
        return $this;
    }
}

?>