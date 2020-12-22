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

class ConfigOptionSelection extends \WHMCS\Model\AbstractModel
{
    use CompoundNameTrait;
    protected $table = "tblproductconfigoptionssub";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = array("configid", "optionname", "sortorder", "hidden");
    protected $casts = array("configid" => "integer", "optionname" => "string", "sortorder" => "integer", "hidden" => "boolean");
    protected $columnMap = array("isHidden" => "hidden");
    protected $configOptionClass = "WHMCS\\Product\\ConfigOption";
    protected $hasSetupFees = NULL;
    protected $hasLinearCyclePricing = NULL;
    public function newCollection(array $models = array())
    {
        return (new \Illuminate\Database\Eloquent\Collection($models))->sortBy("sortorder");
    }
    public function configOption()
    {
        return $this->belongsTo($this->configOptionClass, "configid", "id");
    }
    public function pricingByCurrency(\WHMCS\Billing\Currency $currency)
    {
        $model = \WHMCS\Billing\Pricing::firstOrNew(array("relid" => $this->id, "currency" => $currency->id, "type" => "configoptions"));
        $cycles = new \WHMCS\Billing\Cycles();
        $expectedCycles = $cycles->getRecurringSystemBillingCycles();
        $pricing = array();
        foreach ($expectedCycles as $cycleKey) {
            $setupField = substr($cycleKey, 0, 1) . "setupfee";
            $setupPrice = $model->{$setupField} ? $model->{$setupField} : "0.00";
            $setupFee = new \WHMCS\View\Formatter\Price($setupPrice);
            if (0 < $setupPrice) {
                $this->hasSetupFees = true;
            }
            $cyclePrice = $model->{$cycleKey} ? $model->{$cycleKey} : "0.00";
            $recurringPrice = new \WHMCS\View\Formatter\Price($cyclePrice);
            $pricing[$cycleKey] = new Pricing\Price(array("cycle" => $cycleKey, "price" => $recurringPrice, "setupfee" => $setupFee));
        }
        return $pricing;
    }
    public function hasSetupFees()
    {
        if (is_null($this->hasSetupFees)) {
            $this->hasSetupFees = (bool) \WHMCS\Billing\Pricing::where("relid", $this->id)->where("type", "configoptions")->where(function ($query) {
                $query->where("msetupfee", ">", "0.00")->orWhere("qsetupfee", ">", "0.00")->orWhere("ssetupfee", ">", "0.00")->orWhere("asetupfee", ">", "0.00")->orWhere("bsetupfee", ">", "0.00")->orWhere("tsetupfee", ">", "0.00");
            })->count();
        }
        return $this->hasSetupFees;
    }
    public function hasLinearCyclePricing()
    {
        if (is_null($this->hasLinearCyclePricing)) {
            $models = \WHMCS\Billing\Pricing::where("relid", $this->id)->where("type", "configoptions")->get();
            $expectedCycles = (new \WHMCS\Billing\Cycles())->getRecurringSystemBillingCycles();
            $hasLinear = true;
            foreach ($models as $model) {
                $monthlyRecurring = $model->monthly;
                $monthlySetup = $model->msetupfee;
                foreach ($expectedCycles as $cycle) {
                    $setupField = substr($cycle, 0, 1) . "setupfee";
                    if ($cycle == "monthly") {
                        continue;
                    }
                    $price = new Pricing\Price(array("cycle" => $cycle));
                    if ($price->isYearly()) {
                        $years = (int) $price->cycleInYears();
                        $months = $years * 12;
                    } else {
                        $months = (int) $price->cycleInMonths();
                    }
                    $expectedLinearCyclePrice = number_format($months * $monthlyRecurring, 2, ".", "");
                    $maxDiffRecurring = abs($model->{$cycle} - $expectedLinearCyclePrice);
                    $maxDiffSetup = abs($model->{$setupField} - $monthlySetup);
                    if (1.0E-5 < $maxDiffRecurring || 1.0E-5 < $maxDiffSetup) {
                        $hasLinear = false;
                        break 2;
                    }
                }
            }
            $this->hasLinearCyclePricing = $hasLinear;
        }
        return $this->hasLinearCyclePricing;
    }
    public function getPricing()
    {
        $configuredPricing = array();
        $currencies = \WHMCS\Billing\Currency::all();
        foreach ($currencies as $currency) {
            $configuredPricing[$currency->code] = $this->pricingByCurrency($currency);
        }
        return $configuredPricing;
    }
    public function getPricingByCycle()
    {
        $currencies = \WHMCS\Billing\Currency::all();
        $cycles = new \WHMCS\Billing\Cycles();
        $expectedCycles = $cycles->getRecurringSystemBillingCycles();
        $currencyFill = array_fill_keys($currencies->pluck("code")->toArray(), "0.00");
        $pricing = array_fill_keys($expectedCycles, array("setup" => $currencyFill, "recurring" => $currencyFill));
        foreach ($currencies as $currency) {
            $model = (new \WHMCS\Billing\Pricing())->firstOrNew(array("relid" => $this->id, "type" => "configoptions", "currency" => $currency->id));
            foreach ($expectedCycles as $cycle) {
                $setupField = substr($cycle, 0, 1) . "setupfee";
                if (0 < $model->{$setupField}) {
                    $pricing[$cycle]["setup"][$currency->code] = $model->{$setupField};
                }
                if (0 < $model->{$cycle}) {
                    $pricing[$cycle]["recurring"][$currency->code] = $model->{$cycle};
                }
            }
        }
        return $pricing;
    }
    public function calculateLinearPricing($monthlyRecurring = 0, $monthlySetup = 0)
    {
        $data = array();
        $cycles = new \WHMCS\Billing\Cycles();
        $expectedCycles = $cycles->getRecurringSystemBillingCycles();
        foreach ($expectedCycles as $cycle) {
            $price = new Pricing\Price(array("cycle" => $cycle));
            if ($price->isYearly()) {
                $years = (int) $price->cycleInYears();
                $months = $years * 12;
            } else {
                $months = (int) $price->cycleInMonths();
            }
            $data[$cycle]["recurring"] = number_format($months * $monthlyRecurring, 2, ".", "");
            $data[$cycle]["setup"] = number_format($monthlySetup, 2, ".", "");
        }
        return $data;
    }
    public function setPricing($pricing, $autofillLinearPricing = false, $hasSetupFees = false)
    {
        $cycles = new \WHMCS\Billing\Cycles();
        $expectedCycles = $cycles->getRecurringSystemBillingCycles();
        $currencies = \WHMCS\Billing\Currency::all();
        $this->hasSetupFees = null;
        if ($autofillLinearPricing) {
            $this->hasLinearCyclePricing = true;
        } else {
            $this->hasLinearCyclePricing = null;
        }
        foreach ($currencies as $currency) {
            $code = $currency->code;
            if (!empty($pricing[$code]) && is_array($pricing[$code])) {
                $model = (new \WHMCS\Billing\Pricing())->firstOrNew(array("relid" => $this->id, "type" => "configoptions", "currency" => $currency->id));
                if ($autofillLinearPricing) {
                    $monthlyRecurring = "0.00";
                    $monthlySetup = "0.00";
                    if (isset($pricing[$code]["monthly"]["setup"])) {
                        $monthlySetup = $pricing[$code]["monthly"]["setup"];
                    }
                    if (isset($pricing[$code]["monthly"]["recurring"])) {
                        $monthlyRecurring = $pricing[$code]["monthly"]["recurring"];
                    }
                    $pricing[$code] = $this->calculateLinearPricing($monthlyRecurring, $monthlySetup);
                }
                foreach ($expectedCycles as $cycle) {
                    $setupField = substr($cycle, 0, 1) . "setupfee";
                    $model->{$setupField} = "0.00";
                    $model->{$cycle} = "0.00";
                    if (!empty($pricing[$code][$cycle])) {
                        if ($hasSetupFees && isset($pricing[$code][$cycle]["setup"])) {
                            if (0 < $pricing[$code][$cycle]["setup"]) {
                                $this->hasSetupFees = true;
                            }
                            $model->{$setupField} = $pricing[$code][$cycle]["setup"];
                        }
                        if (isset($pricing[$code][$cycle]["recurring"])) {
                            $model->{$cycle} = $pricing[$code][$cycle]["recurring"];
                        }
                    }
                }
                $model->save();
            }
        }
        if (is_null($this->hasSetupFees)) {
            $this->hasSetupFees = false;
        }
    }
}

?>