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

class AnyEncodingIterator extends AbstractCacheIterator
{
    public function accept(\WHMCS\Environment\Ioncube\Contracts\InspectedFileInterface $current)
    {
        if (in_array($current->getEncoderVersion(), array(\WHMCS\Environment\Ioncube\Contracts\EncodedFileInterface::ENCODER_VERSION_NONE, \WHMCS\Environment\Ioncube\Contracts\EncodedFileInterface::ENCODER_VERSION_UNKNOWN))) {
            return false;
        }
        return true;
    }
}

?>