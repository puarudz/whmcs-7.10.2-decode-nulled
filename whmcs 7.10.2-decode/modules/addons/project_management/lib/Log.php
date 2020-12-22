<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Addon\ProjectManagement;

class Log extends BaseProjectEntity
{
    protected function formatLogTime($date)
    {
        return date("g:ia", strtotime($date));
    }
    public function get()
    {
        $where = array("projectid" => $this->project->id);
        $adminNames = array();
        $result = select_query("tbladmins", "id, firstname, lastname", "");
        while ($data = mysql_fetch_array($result)) {
            $adminNames[$data["id"]] = $data["firstname"] . " " . $data["lastname"];
        }
        $log = array();
        $result = select_query("mod_projectlog", "", $where, "date", "ASC");
        while ($data = mysql_fetch_array($result)) {
            $log[] = array("id" => $data["id"], "date" => fromMySQlDate($data["date"]) . " " . $this->formatLogTime($data["date"]), "message" => $data["msg"], "adminId" => $data["adminid"], "adminName" => $adminNames[$data["adminid"]]);
        }
        return $log;
    }
    public function add($message)
    {
        insert_query("mod_projectlog", array("projectid" => $this->project->id, "date" => "now()", "msg" => $message, "adminid" => Helper::getCurrentAdminId()));
    }
}

?>