<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Http;

trait PriceDataTrait
{
    public function mutatePriceToFull($data = array())
    {
        array_walk_recursive($data, function (&$item) {
            if ($item instanceof \WHMCS\View\Formatter\Price) {
                $item = $item->toFull();
            }
        });
        return $data;
    }
}

?>