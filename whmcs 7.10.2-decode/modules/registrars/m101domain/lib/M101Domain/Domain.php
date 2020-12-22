<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace M101Domain;

class Domain
{
    public $name = NULL;
    public $status = array();
    public $registrant = NULL;
    public $contacts = array();
    public $ns = array();
    public $cr_date = NULL;
    public $up_date = NULL;
    public $ex_date = NULL;
    public $key = NULL;
    protected $lockedStatuses = array("clientTransferProhibited", "clientHold", "serverTransferProhibited", "serverHold");
    public function isLocked()
    {
        foreach ($this->status as $status) {
            if (in_array($status, $this->lockedStatuses)) {
                return true;
            }
        }
        return false;
    }
}

?>