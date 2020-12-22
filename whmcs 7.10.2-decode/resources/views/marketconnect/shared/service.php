<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<div class=\"";
echo $class;
echo " \">\n    <div class=\"panel panel-market-item\" id=\"mpItem";
echo $service;
echo "\">\n        <div class=\"panel-body\">\n            <div class=\"logo\"><img src=\"";
echo file_exists("../assets/img/marketconnect/" . $service . "/logo.svg") ? "../assets/img/marketconnect/" . $service . "/logo.svg" : "../assets/img/marketconnect/" . $service . "/logo.png";
echo "\"></div>\n            <h3>";
echo $data["serviceTitle"];
echo "</h3>\n            <h4>From ";
echo $data["vendorName"];
echo "</h4>\n            <p>";
echo $data["description"];
echo "</p>\n            <div class=\"btn-container\">\n                <div class=\"row\">\n                    <div class=\"col-sm-6\">\n                        <button class=\"btn btn-default btn-block btn-mc-service-control\" onclick=\"openModal('', 'action=showLearnMore&service=";
echo $service;
echo "', '', 'modal-lg', 'modal-mc-service', '', '', '')\" id=\"btnLearnMore-";
echo $service;
echo "\">\n                            Learn more\n                        </button>\n                    </div>\n                    <div class=\"col-sm-6\">\n                        <button class=\"btn btn-inverse btn-block btn-mc-service-control";
echo !$state[$service] ? " hidden" : "";
echo "\" onclick=\"openModal('', 'action=showManage&service=";
echo $service;
echo "', '', 'modal-lg', 'modal-mc-service', '', '', '')\" id=\"btnManage-";
echo $service;
echo "\">\n                            Manage\n                        </button>\n                        <button class=\"btn btn-success btn-block btn-mc-service-control";
echo $state[$service] ? " hidden" : "";
echo "\" onclick=\"openModal('', 'action=showLearnMore&activate=true&service=";
echo $service;
echo "', '', 'modal-lg', 'modal-mc-service', '', '', '')\" id=\"btnStart-";
echo $service;
echo "\">\n                            Start Selling\n                        </button>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n";

?>