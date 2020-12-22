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

class ServerUsageCount extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1900;
    protected $defaultFrequency = 60;
    protected $skipDailyCron = true;
    protected $defaultDescription = "Auto Update Server Usage Count";
    protected $defaultName = "Update Server Usage";
    protected $systemName = "ServerUsageCount";
    public function __invoke()
    {
        $servers = \WHMCS\Product\Server::all();
        foreach ($servers as $server) {
            $moduleInterface = new \WHMCS\Module\Server();
            $moduleInterface->load($server->type);
            $counts = $moduleInterface->call("GetUserCount", $moduleInterface->getServerParams($server));
            if ($counts !== \WHMCS\Module\Server::FUNCTIONDOESNTEXIST) {
                if (array_key_exists("error", $counts)) {
                    continue;
                }
                $remoteData = \WHMCS\Product\Server\Remote::firstOrNew(array("server_id" => $server->id));
                $remoteData->numAccounts = $counts["totalAccounts"];
                $metaData = $remoteData->metaData;
                $metaData["ownedAccounts"] = $counts["ownedAccounts"];
                $remoteData->metaData = $metaData;
                $remoteData->save();
            }
        }
        return $this;
    }
}

?>