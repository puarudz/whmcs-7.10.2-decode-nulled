<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Environment\Ioncube\Contracts;

interface InspectorIteratorInterface extends \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    public function getArrayCopy();
    public function exchangeArray($input);
}

?>