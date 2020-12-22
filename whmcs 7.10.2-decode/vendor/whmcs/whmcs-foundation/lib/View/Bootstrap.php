<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View;

class Bootstrap
{
    public function renderKeyValuePairsInColumns($colWidth, $data)
    {
        $output = "<div class=\"row\">";
        foreach ($data as $values) {
            $output .= "<div class=\"col-sm-" . $colWidth . "\">";
            foreach ($values as $key => $value) {
                if (empty($value)) {
                    $value = "-";
                }
                $output .= $key . ": " . $value . "<br>";
            }
            $output .= "</div>";
        }
        $output .= "</div>";
        return $output;
    }
}

?>