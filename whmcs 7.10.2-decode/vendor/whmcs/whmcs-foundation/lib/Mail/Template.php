<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Mail;

class Template extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblemailtemplates";
    protected $guarded = array("id");
    protected $booleans = array("custom", "disabled", "plaintext");
    protected $commaSeparated = array("attachments", "copyTo", "blindCopyTo");
    public $unique = array();
    public function __toString()
    {
        return $this->name;
    }
    public function scopeMaster($query)
    {
        return $query->where("language", "=", "");
    }
    public static function getActiveLanguages()
    {
        return array_unique(self::where("language", "!=", "")->orderBy("language")->pluck("language")->all());
    }
    public static function boot()
    {
        parent::boot();
        static::creating(function (Template $template) {
            $existingLanguages = Template::where("name", "=", $template->name)->pluck("language")->all();
            if (is_null($existingLanguages)) {
                return true;
            }
            if (!in_array($template->language, $existingLanguages)) {
                return true;
            }
            throw new \WHMCS\Exception\Model\UniqueConstraint("Email template not unique.");
        });
    }
}

?>