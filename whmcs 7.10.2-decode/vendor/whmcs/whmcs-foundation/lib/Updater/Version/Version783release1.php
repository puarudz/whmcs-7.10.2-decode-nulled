<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Updater\Version;

class Version783release1 extends IncrementalVersion
{
    protected $updateActions = array("convertCentovaCastHostnames");
    protected function convertCentovaCastHostnames()
    {
        $servers = \WHMCS\Product\Server::ofModule("centovacast")->get();
        foreach ($servers as $server) {
            $url = $server->hostname;
            if (!preg_match("#^https?://#", $url)) {
                continue;
            }
            $parts = parse_url($url);
            $server->hostname = $parts["host"];
            $server->port = $parts["port"];
            $server->accessHash = $parts["path"];
            $server->secure = strtolower($parts["scheme"]) === "https" ? "on" : "";
            $server->save();
        }
    }
}

?>