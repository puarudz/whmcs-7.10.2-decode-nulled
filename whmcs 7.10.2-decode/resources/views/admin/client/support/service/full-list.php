<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

foreach ($output as $service) {
    echo "    <tr>\n        <td class=\"hidden related-service\">\n            <label>\n                <input type=\"radio\" name=\"related_service[]\" data-type=\"";
    echo $service["type"];
    echo "\" value=\"";
    echo $service["id"];
    echo "\">\n            </label>\n        </td>\n        <td>";
    echo $service["name"];
    echo "</td>\n        <td>";
    echo $service["amount"];
    echo "</td>\n        <td>";
    echo $service["billingCycle"];
    echo "</td>\n        <td>";
    echo $service["registrationDate"];
    echo "</td>\n        <td>";
    echo $service["nextDueDate"];
    echo "</td>\n        <td>";
    echo $service["status"];
    echo "</td>\n    </tr>\n";
}

?>