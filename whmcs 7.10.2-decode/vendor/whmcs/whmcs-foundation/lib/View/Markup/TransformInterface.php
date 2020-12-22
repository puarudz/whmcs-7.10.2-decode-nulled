<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Markup;

interface TransformInterface
{
    const FORMAT_PLAIN = "plain";
    const FORMAT_BBCODE = "bbcode";
    const FORMAT_MARKDOWN = "markdown";
    const FORMAT_HTML = "html";
    public function transform($text, $markupFormat, $emailFriendly);
}

?>