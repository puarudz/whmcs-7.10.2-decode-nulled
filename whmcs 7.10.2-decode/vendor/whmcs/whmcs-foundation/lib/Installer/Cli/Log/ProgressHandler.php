<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Installer\Cli\Log;

class ProgressHandler extends \Monolog\Handler\AbstractProcessingHandler
{
    protected $progressBar = NULL;
    protected $output = NULL;
    public function getProgressBar()
    {
        return $this->progressBar;
    }
    public function setProgressBar($progressBar)
    {
        $this->progressBar = $progressBar;
        return $this;
    }
    public function getOutput()
    {
        return $this->output;
    }
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }
    protected function write(array $record)
    {
        $message = $record["message"];
        if (strpos($message, "Applying Updates Done") === 0) {
            $this->getProgressBar()->advance(1, $record["message"]);
            $finished = false;
            while (empty($finished)) {
                try {
                    $this->getProgressBar()->advance(1, $record["message"]);
                } catch (\Exception $e) {
                    $finished = true;
                }
            }
        } else {
            if (strpos($message, "Applying Updates") === 0) {
                $this->getProgressBar()->advance(1, $record["message"]);
            }
        }
    }
}

?>