<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Apps\Meta\Schema\Version1;

class Remote extends Local
{
    public function getLogoAssetFilename()
    {
        return $this->meta("logo.asset_filename");
    }
    public function getLogoRemoteUri()
    {
        return $this->meta("logo.remote_uri");
    }
    public function getPurchaseFreeTrialDays()
    {
        return $this->meta("purchase.freeTrialDays");
    }
    public function getPurchasePrice()
    {
        return $this->meta("purchase.price");
    }
    public function getPurchaseCurrency()
    {
        return $this->meta("purchase.currency");
    }
    public function getPurchaseTerm()
    {
        return $this->meta("purchase.term");
    }
    public function getPurchaseUrl()
    {
        return $this->meta("purchase.url");
    }
    public function isFeatured()
    {
        return (bool) $this->meta("badges.featured");
    }
    public function isPopular()
    {
        return (bool) $this->meta("badges.popular");
    }
    public function isUpdated()
    {
        return (bool) $this->meta("badges.updated");
    }
    public function getKeywords()
    {
        return $this->meta("keywords");
    }
    public function getWeighting()
    {
        return (int) $this->meta("weighting");
    }
    public function isHidden()
    {
        return (bool) $this->meta("hidden");
    }
}

?>