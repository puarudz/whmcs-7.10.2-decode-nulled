<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Knowledgebase;

class ArticleCategoryLink extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblknowledgebaselinks";
    public $timestamps = false;
    public function article()
    {
        return $this->belongsTo("\\WHMCS\\Knowledgebase\\Article", "articleid");
    }
    public function category()
    {
        return $this->belongsTo("\\WHMCS\\Knowledgebase\\Category", "categoryid");
    }
}

?>