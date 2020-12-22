<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Utilities\System\PhpCompat\View;

abstract class AbstractVersionDetails
{
    protected $style = NULL;
    protected $phpVersion = "";
    protected $phpVersionId = "";
    protected $iterator = NULL;
    protected $ioncubeLoader = NULL;
    public function __construct($phpVersion, $phpVersionId, $iterator, $ioncubeLoader, $whmcsCompat)
    {
        $this->setPhpVersion($phpVersion)->setPhpVersionId($phpVersionId)->setIterator($iterator)->setIoncubeLoader($ioncubeLoader);
    }
    public abstract function getHtml();
    public function getPhpVersion()
    {
        return $this->phpVersion;
    }
    public function setPhpVersion($phpVersion)
    {
        $this->phpVersion = $phpVersion;
        return $this;
    }
    public function getPhpVersionId()
    {
        return $this->phpVersionId;
    }
    public function setPhpVersionId($phpVersionId)
    {
        $this->phpVersionId = $phpVersionId;
        return $this;
    }
    public function getIterator()
    {
        return $this->iterator;
    }
    public function setIterator($iterator)
    {
        $this->iterator = $iterator;
        return $this;
    }
    public function getIoncubeLoader()
    {
        return $this->ioncubeLoader;
    }
    public function setIoncubeLoader($ioncubeLoader)
    {
        $this->ioncubeLoader = $ioncubeLoader;
        return $this;
    }
}

?>