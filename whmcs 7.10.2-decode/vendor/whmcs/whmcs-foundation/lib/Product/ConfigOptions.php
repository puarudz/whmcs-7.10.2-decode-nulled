<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Product;

class ConfigOptions
{
    protected $cache = array();
    protected function getCurrencyID()
    {
        $whmcs = \WHMCS\Application::getInstance();
        return $whmcs->getCurrencyID();
    }
    protected function isCached($productID)
    {
        return isset($this->cache[$productID]) && is_array($this->cache[$productID]);
    }
    protected function getFromCache($productID, $optionLabel)
    {
        if ($this->isCached($productID)) {
            return $this->cache[$productID][$optionLabel];
        }
        return array();
    }
    protected function storeToCache($productID, $optionLabel, $optionsData)
    {
        $this->cache[$productID][$optionLabel] = $optionsData;
        return true;
    }
    protected function loadData($productID)
    {
        $ops = array();
        if (!$this->isCached($productID)) {
            $currencyId = $this->getCurrencyID();
            $info = array();
            $currencyIdInt = (int) $currencyId;
            $productIdInt = (int) $productID;
            $query = "\nSELECT tblproductconfigoptions.id,\n       tblproductconfigoptions.optionname,\n       tblproductconfigoptions.optiontype,\n       tblproductconfigoptions.qtyminimum,\n       tblproductconfigoptions.qtymaximum,\n       (SELECT CONCAT(msetupfee, '|', qsetupfee, '|', ssetupfee, '|', asetupfee, '|', bsetupfee, '|', tsetupfee, '|',\n                      monthly, '|', quarterly, '|', semiannually, '|', annually, '|', biennially, '|', triennially)\n\n        FROM tblpricing,\n        (\n            SELECT DISTINCT parent_sub.configid,\n                            (\n                                SELECT id\n                                FROM tblproductconfigoptionssub as max_sub\n                                WHERE max_sub.configid = parent_sub.configid\n                                  AND max_sub.hidden = 0\n                                ORDER BY sortorder ASC, id ASC\n                                LIMIT 1\n                            ) as id\n            FROM tblproductconfigoptionssub as parent_sub\n        ) as sub_map\n        WHERE tblpricing.type = 'configoptions'\n          AND tblpricing.currency = '" . $currencyIdInt . "'\n          AND tblpricing.relid = sub_map.id\n          AND sub_map.configid = tblproductconfigoptions.id\n        )\nFROM tblproductconfigoptions\n         INNER JOIN tblproductconfiglinks ON tblproductconfigoptions.gid = tblproductconfiglinks.gid\nWHERE tblproductconfiglinks.pid = '" . $productIdInt . "'\n  AND tblproductconfigoptions.hidden = 0;\n            ";
            $result = full_query($query);
            while ($data = mysql_fetch_array($result)) {
                $info[$data[0]] = array("name" => $data["optionname"], "type" => $data["optiontype"], "qtyminimum" => $data["qtyminimum"], "qtymaximum" => $data["qtymaximum"]);
                $ops[$data[0]] = explode("|", $data[5]);
            }
            $this->storeToCache($productID, "info", $info);
            $this->storeToCache($productID, "pricing" . $currencyId, $ops);
        }
        return $ops;
    }
    public function getBasePrice($productID, $billingCycle)
    {
        $cycles = new \WHMCS\Billing\Cycles();
        if ($cycles->isValidSystemBillingCycle($billingCycle)) {
            $this->loadData($productID);
            $optionsInfo = $this->getFromCache($productID, "info");
            $optionsPricing = $this->getFromCache($productID, "pricing" . $this->getCurrencyID());
            $pricingObj = new \WHMCS\Billing\LegacyPricing();
            $cycleindex = array_search($billingCycle, $pricingObj->getDBFields());
            $price = 0;
            foreach ($optionsPricing as $configID => $pricing) {
                if ($optionsInfo[$configID]["type"] == 1 || $optionsInfo[$configID]["type"] == 2) {
                    $price += $pricing[$cycleindex];
                } else {
                    if ($optionsInfo[$configID]["type"] != 3) {
                        if ($optionsInfo[$configID]["type"] == 4) {
                            $minquantity = $optionsInfo[$configID]["qtyminimum"];
                            if (0 < $minquantity) {
                                $price += $minquantity * $pricing[$cycleindex];
                            }
                        }
                    }
                }
            }
            return $price;
        } else {
            return false;
        }
    }
    public function hasConfigOptions($productID)
    {
        $this->loadData($productID);
        $optionsInfo = $this->getFromCache($productID, "info");
        if (0 < count($optionsInfo)) {
            return true;
        }
        return false;
    }
}

?>