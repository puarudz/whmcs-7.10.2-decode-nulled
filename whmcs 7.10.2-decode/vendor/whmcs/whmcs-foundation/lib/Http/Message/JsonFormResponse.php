<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Http\Message;

class JsonFormResponse extends JsonResponse
{
    public static function createWithSuccess($data = NULL)
    {
        return new static(array("data" => $data));
    }
    public static function createWithErrors(array $data)
    {
        return new static(array("fields" => $data), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
    }
}

?>