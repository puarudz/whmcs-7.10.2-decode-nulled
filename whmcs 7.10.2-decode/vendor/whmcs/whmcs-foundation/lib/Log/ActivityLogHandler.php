<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Log;

class ActivityLogHandler extends \Monolog\Handler\AbstractProcessingHandler
{
    protected function write(array $record)
    {
        if ($record["formatted"]) {
            try {
                $event = array("date" => (string) \WHMCS\Carbon::now()->format("YmdHis"), "description" => $record["formatted"], "user" => "", "userid" => "", "ipaddr" => "");
                \WHMCS\Database\Capsule::table("tblactivitylog")->insertGetId($event);
            } catch (\Exception $e) {
            }
        }
    }
}

?>