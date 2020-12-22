<?php

namespace WHMCS\Utility;

class GeoIp
{
    public static function getLookupUrl($ip)
    {
        $ip = preg_replace("/[^a-z0-9:\\.]/i", "", $ip);
        $link = "https://whatsmyip.mobi/?ip_address=" . $ip;
        return $link;
    }
    public static function getLookupHtmlAnchor($ip, $classes = NULL, $text = NULL)
    {
        $link = static::getLookupUrl($ip);
        if (is_null($classes)) {
            $classes .= "autoLinked";
        } else {
            if ($classes && is_string($classes)) {
                $classes .= " autoLinked";
            } else {
                $classes = "";
            }
        }
        $text = (string) $text;
        if (!strlen($text)) {
            $text = $ip;
        }
        return sprintf("<a href=\"%s\" class=\"%s\" >%s</a>", $link, $classes, $text);
    }
}

?>