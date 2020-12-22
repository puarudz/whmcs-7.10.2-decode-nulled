<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS;

class Mobile extends Admin
{
    public function getTemplatePath()
    {
        if (!defined("MOBILEDIR")) {
            exit("No Mobile Directory Defined");
        }
        return MOBILEDIR . "/templates/";
    }
    protected function factoryAdminSmarty()
    {
        $smarty = parent::factoryAdminSmarty();
        $smarty->template_dir = $this->getTemplatePath();
        return $smarty;
    }
    public function output()
    {
        $this->smarty->display("header.tpl");
        $content = $this->smarty->fetch($this->template . ".tpl");
        $content = preg_replace("/(<form\\W[^>]*\\bmethod=('|\"|)POST('|\"|)\\b[^>]*>)/i", "\\1" . "\n" . generate_token(), $content);
        if ($this->exitmsg) {
            $content = $this->exitmsg;
        }
        echo $content;
        $this->smarty->display("footer.tpl");
    }
    public function setPageTitle($title)
    {
        $this->title = $title;
        return true;
    }
    public function setHeaderLeftBtn($url, $label = "", $icon = "")
    {
        if ($url == "back") {
            $url = "\" data-rel=\"back";
            $label = "Back";
            $icon = "back";
        }
        if ($url == "home") {
            $url = "index.php";
            $label = "Home";
            $icon = "home";
        }
        $this->assign("headleftbtnurl", $url);
        $this->assign("headleftbtnlabel", $label);
        $this->assign("headleftbtnicon", $icon);
    }
    public function setHeaderRightBtn($url, $label, $icon = "")
    {
        $this->assign("headrightbtnurl", $url);
        $this->assign("headrightbtnlabel", $label);
        $this->assign("headrightbtnicon", $icon);
    }
}

?>