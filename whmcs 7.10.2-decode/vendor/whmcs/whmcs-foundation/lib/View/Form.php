<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View;

class Form
{
    private $url = "";
    private $method = "";
    private $params = array();
    private $submitLabel = "";
    const METHOD_GET = "get";
    const METHOD_POST = "post";
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }
    public function setUriPrefixAdminBaseUrl($uri)
    {
        return $this->setUri(\WHMCS\Utility\Environment\WebHelper::getAdminBaseUrl() . "/" . $uri);
    }
    public function setUriByRoutePath($routePath)
    {
        return $this->setUri(routePath($routePath));
    }
    public function getUri()
    {
        return $this->uri;
    }
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function setParameters(array $params)
    {
        $this->params = $params;
        return $this;
    }
    public function getParameters()
    {
        return $this->params;
    }
    public function setSubmitLabel($submitLabel)
    {
        $this->submitLabel = $submitLabel;
        return $this;
    }
    public function getSubmitLabel()
    {
        return $this->submitLabel;
    }
}

?>