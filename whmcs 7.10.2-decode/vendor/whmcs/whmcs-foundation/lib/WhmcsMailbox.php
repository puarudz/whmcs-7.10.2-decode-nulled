<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS;

class WhmcsMailbox extends \PhpImap\Mailbox
{
    public function __construct($imapPath, $login, $password, $attachmentsDir = NULL, $serverEncoding = "UTF-8")
    {
        imap_errors();
        parent::__construct($imapPath, $login, $password, $attachmentsDir, $serverEncoding);
    }
    protected function convertStringEncoding($string, $fromEncoding, $toEncoding)
    {
        if (strcasecmp($fromEncoding, "iso-8859-8-i") == 0) {
            $fromEncoding = "iso-8859-8";
        }
        return parent::convertStringEncoding($string, $fromEncoding, $toEncoding);
    }
}

?>