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

class CloseInactiveTickets extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1610;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Auto Close Inactive Tickets";
    protected $defaultName = "Inactive Tickets";
    protected $systemName = "CloseInactiveTickets";
    protected $outputs = array("closed" => array("defaultValue" => 0, "identifier" => "closed", "name" => "Closed"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"));
    protected $icon = "fas fa-ticket-alt";
    protected $successCountIdentifier = "closed";
    protected $successKeyword = "Closed";
    protected $hasDetail = true;
    public function __invoke()
    {
        $this->setDetails(array("success" => array()));
        $whmcs = \DI::make("app");
        if (!$whmcs->get_config("CloseInactiveTickets")) {
            return $this;
        }
        $departmentresponders = array();
        $result = select_query("tblticketdepartments", "id,noautoresponder", "");
        while ($data = mysql_fetch_array($result)) {
            $id = $data["id"];
            $noautoresponder = $data["noautoresponder"];
            $departmentresponders[$id] = $noautoresponder;
        }
        $closetitles = array();
        $result = select_query("tblticketstatuses", "title", array("autoclose" => "1"));
        while ($data = mysql_fetch_array($result)) {
            $closetitles[] = $data[0];
        }
        if ($closetitles) {
            $ticketCloseCutoff = \WHMCS\Carbon::now()->subHours($whmcs->get_config("CloseInactiveTickets"));
            $ticketIdsToClose = \WHMCS\Support\Ticket::whereIn("status", $closetitles)->where("lastreply", "<=", $ticketCloseCutoff)->pluck("id");
            foreach ($ticketIdsToClose as $ticketId) {
                $ticket = \WHMCS\Support\Ticket::find($ticketId);
                if (!$ticket) {
                    continue;
                }
                if (!in_array($ticket->status, $closetitles)) {
                    continue;
                }
                if ($ticket->lastReply->gt($ticketCloseCutoff)) {
                    continue;
                }
                closeTicket($ticket->id);
                if (!$departmentresponders[$ticket->departmentId] && !$whmcs->get_config("TicketFeedback")) {
                    sendMessage("Support Ticket Auto Close Notification", $ticket->id);
                }
                $this->addSuccess(array("ticket", $ticket->id, ""));
            }
        }
        $this->output("closed")->write(count($this->getSuccesses()));
        $this->output("action.detail")->write(json_encode($this->getDetail()));
        return $this;
    }
}

?>