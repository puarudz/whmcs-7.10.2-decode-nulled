<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Environment\Ioncube\Inspector\Filter;

class EncoderFingerprintFavorV9 extends AbstractAbsolutelyNonDecodableIterator
{
    public function getAssessment(\WHMCS\Environment\Ioncube\Contracts\InspectedFileInterface $file)
    {
        return $file->getAnalyzer()->versionCompatibilityAssessment($this->getPhpVersion());
    }
}

?>