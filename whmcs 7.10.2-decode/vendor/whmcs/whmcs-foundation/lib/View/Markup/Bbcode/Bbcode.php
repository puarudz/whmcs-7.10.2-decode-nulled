<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Markup\Bbcode;

class Bbcode
{
    public static function transform($text)
    {
        $bbCodeMap = array("b" => "strong", "i" => "em", "u" => "ul", "div" => "div");
        $text = preg_replace("/\\[div=(&quot;|\")(.*?)(&quot;|\")\\]/", "<div class=\"\$2\">", $text);
        foreach ($bbCodeMap as $bbCode => $htmlCode) {
            $text = str_replace("[" . $bbCode . "]", "<" . $htmlCode . ">", $text);
            $text = str_replace("[/" . $bbCode . "]", "</" . $htmlCode . ">", $text);
        }
        return $text;
    }
}

?>