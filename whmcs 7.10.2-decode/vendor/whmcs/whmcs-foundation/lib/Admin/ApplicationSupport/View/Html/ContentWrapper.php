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

class ContentWrapper extends \WHMCS\Http\Message\AbstractViewableResponse implements \WHMCS\View\HtmlPageInterface
{
    use \WHMCS\Admin\ApplicationSupport\View\Traits\BodyContentTrait;
    public function __construct($data = "", $status = 200, array $headers = array())
    {
        parent::__construct($data, $status, $headers);
        $this->setBodyContent($data);
    }
    protected function getOutputContent()
    {
        $html = $this->getFormattedBodyContent();
        return (new \WHMCS\Admin\ApplicationSupport\View\PreRenderProcessor())->process($html);
    }
    public function getFormattedHtmlHeadContent()
    {
        return "";
    }
    public function getFormattedHeaderContent()
    {
        return "";
    }
    public function getFormattedBodyContent()
    {
        return $this->getBodyContent();
    }
    public function getFormattedFooterContent()
    {
        return "";
    }
}

?>