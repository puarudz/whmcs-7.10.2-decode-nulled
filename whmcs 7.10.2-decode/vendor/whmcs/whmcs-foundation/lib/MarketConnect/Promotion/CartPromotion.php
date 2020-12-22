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

class CartPromotion extends Promotion
{
    protected function getTemplate()
    {
        $orderFormTemplate = \WHMCS\View\Template\OrderForm::factory("marketconnect-promo.tpl");
        return $orderFormTemplate->getTemplatePath() . "marketconnect-promo.tpl";
    }
}

?>