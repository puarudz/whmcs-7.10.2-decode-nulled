<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Mail\Entity;

class Notification extends \WHMCS\Mail\Emailer
{
    protected $isNonClientEmail = true;
    public function __construct($message, $entityId, $extraParams = NULL)
    {
        parent::__construct($message, $entityId, $extraParams);
        if (array_key_exists("senderName", $extraParams)) {
            $this->message->setFromName($this->extraParams["senderName"]);
            unset($this->extraParams["senderName"]);
        }
        if (array_key_exists("senderEmail", $extraParams)) {
            $this->message->setFromEmail($this->extraParams["senderEmail"]);
            unset($this->extraParams["senderEmail"]);
        }
        if (is_array($extraParams) && array_key_exists("to", $extraParams)) {
            foreach ($extraParams["to"] as $to) {
                $this->message->addRecipient("to", trim($to));
            }
            unset($this->extraParams["to"]);
        }
    }
    public function getEntitySpecificMergeData($id, $extra)
    {
    }
}

?>