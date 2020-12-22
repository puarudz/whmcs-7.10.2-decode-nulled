<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Exception\Handler;

class TerminusHandler extends \Whoops\Handler\Handler
{
    public function handle()
    {
        $exception = $this->getException();
        $terminus = \WHMCS\Terminus::getInstance();
        if ($exception instanceof \WHMCS\Exception\ProgramExit) {
            if ($msg = $exception->getMessage()) {
                echo $msg;
            }
            $terminus->doExit(1);
            return \Whoops\Handler\Handler::LAST_HANDLER;
        }
        if ($exception instanceof \WHMCS\Exception\Fatal) {
            if (defined("IN_CRON") || \WHMCS\Environment\Php::isCli()) {
                $msg = $exception->getMessage();
            } else {
                $errorPage = new \WHMCS\View\HtmlErrorPage();
                $errorPage->body .= "<p>Error: " . $exception->getMessage() . "</p>";
                $msg = $errorPage->getHtmlErrorPage();
            }
            $terminus->doDie($msg);
            return \Whoops\Handler\Handler::LAST_HANDLER;
        }
        return \Whoops\Handler\Handler::DONE;
    }
}

?>