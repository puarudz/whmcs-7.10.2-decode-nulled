<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\ApplicationSupport\View\Html\Smarty;

class TemplateBody extends BodyContentWrapper
{
    public function __construct($bodyTemplateName)
    {
        parent::__construct();
        $this->setTemplateName($bodyTemplateName);
    }
    public function getBodyContent()
    {
        if (!$this->bodyContent) {
            $this->bodyContent = "";
            $smarty = $this->getTemplateEngine();
            if ($this->getTemplateName()) {
                $this->bodyContent = $smarty->fetch($this->getTemplateDirectory() . "/" . $this->getTemplateName() . ".tpl");
            }
        }
        return $this->bodyContent;
    }
}

?>