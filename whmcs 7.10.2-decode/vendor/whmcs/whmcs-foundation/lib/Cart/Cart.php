<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Cart;

class Cart
{
    public $invoiceId = 0;
    public $items = array();
    public $total = NULL;
    public $taxCalculator = NULL;
    public $client = NULL;
    public static function fromSession()
    {
        if (!function_exists("calcCartTotals")) {
            require_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "orderfunctions.php";
        }
        global $currency;
        $currency = getCurrency(\WHMCS\Session::get("uid"), \WHMCS\Session::get("currency"));
        $cartData = calcCartTotals(false, true, $currency);
        $items = array();
        foreach ($cartData["products"] as $product) {
            $productItem = (new Item\Product())->setId("pid-" . $product["pid"])->setName($product["productinfo"]["groupname"] . " - " . $product["productinfo"]["name"])->setBillingCycle($product["billingcycle"])->setQuantity($product["qty"])->setAmount($product["pricing"]["totaltodayexcltax"])->setRecurring(is_array($product["pricing"]["recurringexcltax"]) && 0 < count($product["pricing"]["recurringexcltax"]) ? current($product["pricing"]["recurringexcltax"]) : null)->setTaxed($product["taxed"]);
            if (array_key_exists("proratadate", $product)) {
                try {
                    $days = \WHMCS\Carbon::createFromFormat("Y-m-d", toMySQLDate($product["proratadate"]));
                    $productItem->setInitialPeriod($days->diffInDays(), "days");
                } catch (\Exception $e) {
                }
            }
            $items[] = $productItem;
            foreach ($product["addons"] as $addon) {
                $items[] = (new Item\Addon())->setId("aid-" . $addon["addonid"])->setName($addon["name"])->setBillingCycle($addon["billingcycle"])->setQuantity(1)->setAmount($addon["totaltoday"])->setRecurring($addon["isRecurring"] === true ? $addon["recurring"] : null)->setTaxed($addon["taxed"]);
            }
        }
        foreach ($cartData["addons"] as $addon) {
            $items[] = (new Item\Addon())->setId("aid-" . $addon["addonid"])->setName($addon["name"])->setBillingCycle($addon["billingcycle"])->setQuantity(1)->setAmount($addon["totaltoday"])->setRecurring($addon["isRecurring"] === true ? $addon["recurring"] : null)->setTaxed($addon["taxed"]);
        }
        foreach ($cartData["domains"] as $domain) {
            $itemName = $domain["type"] == "register" ? "Domain Registration" : "Domain Transfer";
            $domainParts = explode(".", $domain["domain"], 2);
            $items[] = (new Item\Domain())->setId("domain-" . $domainParts[1])->setName($itemName)->setBillingCycle("annually")->setBillingPeriod($domain["regperiod"])->setAmount($domain["totaltoday"])->setRecurring($domain["renewprice"])->setTaxed($domain["taxed"]);
        }
        foreach ($cartData["renewals"] as $domain) {
            $domainParts = explode(".", $domain["domain"], 2);
            $items[] = (new Item\Item())->setId("renewal-" . $domainParts[1])->setName("Domain Renewal")->setBillingCycle("annually")->setBillingPeriod($domain["regperiod"])->setAmount($domain["price"])->setRecurring($domain["priceWithoutGraceAndRedemption"])->setTaxed($domain["taxed"]);
        }
        return (new self())->setClient(\WHMCS\User\Client::loggedIn()->first())->setItems($items)->applyTax()->setTotal($cartData["total"])->applyClientGroupDiscount();
    }
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }
    public function getInvoiceModel()
    {
        return \WHMCS\Billing\Invoice::find($this->invoiceId);
    }
    public function setClient(\WHMCS\User\Client $client = NULL)
    {
        $this->client = $client;
        return $this;
    }
    public function setItems($items)
    {
        $this->items = collect($items);
        return $this;
    }
    public function setTotal(\WHMCS\View\Formatter\Price $total)
    {
        $this->total = $total;
        return $this;
    }
    public function getFirstRecurringItem()
    {
        foreach ($this->items as $item) {
            if ($item->recurring) {
                return $item;
            }
        }
        return null;
    }
    public function isRecurring()
    {
        if (0 < $this->items->count()) {
            return !is_null($this->getFirstRecurringItem());
        }
        return false;
    }
    public function getRecurringTotals()
    {
        $recurringTotals = array();
        foreach ($this->items as $item) {
            if (!is_null($item->recurring) && !$item->hasInitialPeriod()) {
                $recurringTotal = $item->recurring->toNumeric();
                if (!isset($recurringTotals[$item->billingCycle][$item->billingPeriod])) {
                    $recurringTotals[$item->billingCycle][$item->billingPeriod] = 0;
                }
                $recurringTotals[$item->billingCycle][$item->billingPeriod] += $recurringTotal;
            }
        }
        foreach ($recurringTotals as $cycle => &$periods) {
            foreach ($periods as $period => &$value) {
                $value = format_as_currency($value);
            }
        }
        return $recurringTotals;
    }
    public function getRecurringTotal()
    {
        $firstItem = $this->getFirstRecurringItem();
        if (!$firstItem) {
            return null;
        }
        if ($firstItem->hasInitialPeriod()) {
            $total = 0;
            foreach ($this->items as $item) {
                if ($item->hasInitialPeriod() && $item->billingPeriod == $firstItem->billingPeriod && $item->billingCycle == $firstItem->billingCycle && $item->initialPeriod == $firstItem->initialPeriod && $item->initialCycle == $firstItem->initialCycle) {
                    $total += $item->recurring->toNumeric();
                }
            }
            return format_as_currency($total);
        } else {
            return $this->getRecurringTotals()[$firstItem->billingCycle][$firstItem->billingPeriod];
        }
    }
    public function setTaxCalculator(\WHMCS\Billing\Tax $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
        return $this;
    }
    public function getTaxCalculator(\WHMCS\User\Client $client)
    {
        if ($this->taxCalculator) {
            return $this->taxCalculator;
        }
        $taxCalculator = (new \WHMCS\Billing\Tax())->setIsInclusive(\WHMCS\Config\Setting::getValue("TaxType") == "Inclusive")->setIsCompound(\WHMCS\Config\Setting::getValue("TaxL2Compound"));
        require_once ROOTDIR . "/includes/invoicefunctions.php";
        $taxdata = getTaxRate(1, $client->state, $client->country);
        $taxCalculator->setLevel1Percentage($taxdata["rate"]);
        $taxdata = getTaxRate(2, $client->state, $client->country);
        $taxCalculator->setLevel2Percentage($taxdata["rate"]);
        return $taxCalculator;
    }
    public function applyTax()
    {
        if (!\WHMCS\Config\Setting::getValue("TaxEnabled")) {
            return $this;
        }
        $client = $this->client;
        if (!$client) {
            $cartSessionData = \WHMCS\Session::get("cart");
            $client = new \WHMCS\User\Client();
            $client->state = isset($cartSessionData["user"]["state"]) ? $cartSessionData["user"]["state"] : "";
            $client->country = isset($cartSessionData["user"]["country"]) ? $cartSessionData["user"]["country"] : \WHMCS\Config\Setting::getValue("DefaultCountry");
        }
        if ($client->taxExempt) {
            return $this;
        }
        $taxCalculator = $this->getTaxCalculator($client);
        foreach ($this->items as $item) {
            if ($item->taxed) {
                if ($item->amount && 0 < $item->amount) {
                    $item->amount = new \WHMCS\View\Formatter\Price($taxCalculator->setTaxBase($item->amount->toNumeric())->getTotalAfterTaxes(), $item->amount->getCurrency());
                }
                if ($item->recurring && 0 < $item->recurring) {
                    $item->recurring = new \WHMCS\View\Formatter\Price($taxCalculator->setTaxBase($item->recurring->toNumeric())->getTotalAfterTaxes(), $item->recurring->getCurrency());
                }
            }
        }
        if (!is_null($this->total)) {
            $this->total = new \WHMCS\View\Formatter\Price($taxCalculator->setTaxBase($this->total->toNumeric())->getTotalAfterTaxes(), $this->total->getCurrency());
        }
        return $this;
    }
    public function getDescription()
    {
        if ($this->isRecurring()) {
            $firstItem = $this->getFirstRecurringItem();
            if (0 < strlen($firstItem->name)) {
                return $firstItem->name;
            }
        }
        if (0 < $this->invoiceId) {
            return "Invoice #" . $this->invoiceId;
        }
        return "Shopping Cart Checkout";
    }
    public function applyClientGroupDiscount()
    {
        $clientGroupDiscount = 0;
        if ($this->client && $this->client instanceof \WHMCS\User\Client) {
            $clientGroupDiscount = $this->client->getClientDiscountPercentage();
        }
        if (0 < $clientGroupDiscount) {
            $discount = 1 - $clientGroupDiscount / 100;
            foreach ($this->items as $item) {
                $amount = $item->amount;
                $itemCurrency = $amount->getCurrency();
                $amount = $amount->toNumeric() * $discount;
                $amount = round($amount, 2);
                $item->setAmount(new \WHMCS\View\Formatter\Price($amount, $itemCurrency));
                if ($item->recurring) {
                    $recurringAmount = $item->recurring->toNumeric() * $discount;
                    $recurringAmount = round($recurringAmount, 2);
                    $item->setRecurring(new \WHMCS\View\Formatter\Price($recurringAmount, $itemCurrency));
                }
            }
            if (!is_null($this->total)) {
                $totalAmount = $this->total->toNumeric();
                $totalCurrency = $this->total->getCurrency();
                $totalAmount = $totalAmount * $discount;
                $this->total = new \WHMCS\View\Formatter\Price($totalAmount, $totalCurrency);
            }
        }
        return $this;
    }
    public function getTotal()
    {
        $total = $this->total;
        if (is_null($total)) {
            $total = new \WHMCS\View\Formatter\Price(0);
        }
        return $total;
    }
}

?>