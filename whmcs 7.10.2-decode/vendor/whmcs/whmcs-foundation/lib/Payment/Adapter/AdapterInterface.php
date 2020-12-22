<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\Adapter;

interface AdapterInterface
{
    public function getConfigurationParameters();
    public function setConfigurationParameters(array $configuration);
    public function getSolutionType();
    public function setSolutionType($type);
    public function isLinkCapable();
    public function isCaptureCapable();
    public function isRefundCapable();
    public function isRemotePaymentDetailsStorageCapable();
    public function getHtmlLink(array $params);
    public function captureTransaction(array $params);
    public function refundTransaction(array $params);
    public function storePaymentDetailsRemotely(array $params);
}

?>