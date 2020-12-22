<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\User\Client;

class SecurityQuestion extends \WHMCS\Model\AbstractModel
{
    protected $table = "tbladminsecurityquestions";
    public function getQuestionAttribute($question)
    {
        return decrypt($question);
    }
    public function setQuestionAttribute($question)
    {
        $this->attributes["question"] = encrypt($question);
    }
    public function clients()
    {
        return $this->hasMany("WHMCS\\User\\Client", "securityqid");
    }
}

?>