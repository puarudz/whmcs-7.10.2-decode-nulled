<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment;

class PaymentGatewayProductMapping extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblpaymentgateways_product_mapping";
    public $timestamps = true;
    public function createTable($drop = false)
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id");
                $table->string("gateway")->default("");
                $table->string("account_identifier")->default("");
                $table->string("product_identifier")->default("");
                $table->string("remote_identifier")->default("");
                $table->timestamps();
            });
        }
    }
    public function scopeGateway($query, $gateway)
    {
        return $query->where("gateway", $gateway);
    }
    public function scopeAccountIdentifier($query, $accountIdentifier)
    {
        return $query->where("account_identifier", $accountIdentifier);
    }
    public function scopeProductIdentifier($query, $productIdentifier)
    {
        return $query->where("product_identifier", $productIdentifier);
    }
}

?>