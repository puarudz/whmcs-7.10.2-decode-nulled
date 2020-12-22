<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\ApplicationSupport\View\Html;

class PopUp extends AbstractNoEngine
{
    public function __construct($data = "", $status = 200, array $headers = array())
    {
        parent::__construct($data, $status, $headers);
        $this->setBodyContent($data);
    }
    public function getFormattedHeaderContent()
    {
        $html = "<body class=\"popup-body\">\n    <div class=\"popup-content-area\">\n        <table width=\"100%\" bgcolor=\"#ffffff\" cellpadding=\"15\"><tr><td>\n\n        <h2>" . $this->getTitle() . "</h2>\n";
        return $html;
    }
    public function getFormattedFooterContent()
    {
        $html = "        \n        </td></tr></table>\n    </div>\n</body>\n</html>";
        return $html;
    }
}

?>