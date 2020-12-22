<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\MarketConnect\Promotion;

class LoginPanel extends \WHMCS\View\Client\HomepagePanel
{
    protected $requiresDomain = true;
    protected $dropdownReplacementText = "";
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
    public function setRequiresDomain($requiresDomain)
    {
        $this->requiresDomain = $requiresDomain;
        return $this;
    }
    public function setDropdownReplacementText($replacementText)
    {
        $this->dropdownReplacementText = $replacementText;
        return $this;
    }
    public function setPoweredBy($poweredBy)
    {
        $this->poweredBy = $poweredBy;
        return $this;
    }
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }
    protected function buildServicesDropdown()
    {
        $dropdownValues = array();
        foreach ($this->services as $service) {
            $dropdownValues[] = "<option value=\"" . ($service["type"] == "addon" ? "a" : "") . $service["id"] . "\">" . $service["domain"] . "</option>";
        }
        return implode(PHP_EOL, $dropdownValues);
    }
    public function getBodyHtml()
    {
        $replacementText = "";
        if ($this->requiresDomain) {
            $serviceSelect = "Choose Domain:" . "<select name=\"service-id\" class=\"form-control\">" . $this->buildServicesDropdown() . "</select>";
        } else {
            $firstService = $this->services[0];
            $firstServiceId = $firstService["id"];
            if ($firstService["type"] == "addon") {
                $firstServiceId = "a" . $firstServiceId;
            }
            $serviceSelect = "<input type=\"hidden\" name=\"service-id\" value=\"" . $firstServiceId . "\">";
            $replacementText = $this->dropdownReplacementText;
        }
        return "<div class=\"panel-mc-sso\">\n    <div class=\"row\">\n        <div class=\"col-sm-6 text-center\">\n            <img src=\"" . $this->image . "\">\n        </div>\n        <div class=\"col-sm-6\">\n            <form action=\"" . routePath("upgrade") . "\" method=\"post\">\n                <input type=\"hidden\" name=\"action\" value=\"manage-service\" />\n                " . $replacementText . "\n                " . $serviceSelect . "\n                <button class=\"btn btn-default btn-service-sso\">\n                    <span class=\"loading hidden\">\n                        <i class=\"fas fa-spinner fa-spin\"></i>\n                    </span>\n                    <span class=\"text\">" . \Lang::trans("manage") . "</span>\n                </button>\n                <span class=\"login-feedback\"></span>\n            </form>\n            <small>Powered by " . $this->poweredBy . "&trade;</small>\n        </div>\n    </div>\n</div>";
    }
    public function toHtml()
    {
        $serviceType = $this->services[0]["type"];
        $serviceId = $this->services[0]["id"];
        return "<div class=\"panel panel-default\" id=\"" . $this->name . "\">\n        <div class=\"panel-heading\">\n            <h3 class=\"panel-title\">" . $this->label . "</h3>\n        </div>\n        <div class=\"panel-body\">\n            <form action=\"" . routePath("upgrade") . "\" method=\"post\">\n                <img src=\"" . $this->image . "\" width=\"175\">\n                <input type=\"hidden\" name=\"action\" value=\"manage-service\" />\n                <input type=\"hidden\" name=\"service-id\" value=\"" . ($serviceType == "addon" ? "a" : "") . $serviceId . "\">\n                <input type=\"hidden\" name=\"isproduct\" value=\"" . (int) ($serviceType == "service") . "\">\n                <input type=\"hidden\" name=\"serviceid\" value=\"" . $serviceId . "\">\n                <button class=\"btn btn-default btn-service-sso\">\n                    <span class=\"loading hidden\">\n                        <i class=\"fas fa-spinner fa-spin\"></i>\n                    </span>\n                    <span class=\"text\">" . \Lang::trans("manage") . "</span>\n                </button>\n                <button type=\"submit\" class=\"btn btn-default\">\n                    " . \Lang::trans("upgrade") . "\n                </button>\n                <span class=\"login-feedback\"></span>\n            </form>\n        </div>\n    </div>";
    }
}

?>