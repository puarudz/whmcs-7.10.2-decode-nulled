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

class JsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse
{
    use DataTrait;
    use PriceDataTrait;
    public function setData($data = array())
    {
        $data = $this->mutatePriceToFull($data);
        $this->setRawData($data);
        parent::setData($data);
        return $this;
    }
}

?>