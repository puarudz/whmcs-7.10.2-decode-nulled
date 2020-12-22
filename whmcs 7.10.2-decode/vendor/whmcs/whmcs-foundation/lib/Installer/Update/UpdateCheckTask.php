<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Installer\Update;

class UpdateCheckTask extends \WHMCS\Scheduling\Task\AbstractTask
{
    public $description = "WHMCS Update Check";
    protected $frequency = "0 */8 * * *";
    public function __construct()
    {
        parent::__construct();
        $this->preventOverlapping();
    }
    public function __invoke()
    {
        $this->getOutput()->debug("a debug message", array("PreviousCheck" => \WHMCS\Config\Setting::getValue("UpdatesLastChecked")));
        $this->getOutput()->info("Fetching Update Info");
        $updater = new Updater();
        return $updater->updateRemoteComposerData();
    }
}

?>