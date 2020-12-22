<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Markup\Error\Message\MatchDecorator\FilePermission;

class PostCommandCopy extends \WHMCS\View\Markup\Error\Message\MatchDecorator\AbstractMatchDecorator
{
    use \WHMCS\View\Markup\Error\Message\MatchDecorator\GenericMatchDecorationTrait;
    const PATTERN_DIRECTORY_UNABLE_TO_COPY = "/Unable to copy (.*) to (.*)/";
    const PATTERN_UNABLE_TO_PERFORM_EARLY_FILE_COPY = "/Failed to perform early file copy during WHMCS file relocation/";
    public function getTitle()
    {
        return "Insufficient File Permissions For Deployment";
    }
    public function getHelpUrl()
    {
        return "https://docs.whmcs.com/Automatic_Updater#Permission_Errors";
    }
    protected function isKnown($data)
    {
        return preg_match(self::PATTERN_DIRECTORY_UNABLE_TO_COPY, $data) || preg_match(self::PATTERN_UNABLE_TO_PERFORM_EARLY_FILE_COPY, $data);
    }
}

?>