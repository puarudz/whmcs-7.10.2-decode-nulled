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

class ServerRemoteMetaData extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1910;
    protected $defaultFrequency = 60;
    protected $skipDailyCron = true;
    protected $defaultDescription = "Auto Update Server Meta Data";
    protected $defaultName = "Update Server Meta Data";
    protected $systemName = "ServerRemoteMetaData";
    public function __invoke()
    {
        $servers = \WHMCS\Product\Server::all();
        foreach ($servers as $server) {
            $moduleInterface = new \WHMCS\Module\Server();
            $moduleInterface->load($server->type);
            $serverMetaData = $moduleInterface->call("GetRemoteMetaData", $moduleInterface->getServerParams($server));
            if ($serverMetaData !== \WHMCS\Module\Server::FUNCTIONDOESNTEXIST) {
                if (array_key_exists("error", $serverMetaData)) {
                    continue;
                }
                $remoteData = \WHMCS\Product\Server\Remote::firstOrNew(array("server_id" => $server->id));
                $metaData = $remoteData->metaData;
                $metaData = array_merge($metaData, $serverMetaData);
                $remoteData->metaData = $metaData;
                $remoteData->save();
            }
        }
        return $this;
    }
}

?>