<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Domains\Extension;

class Pricing extends \WHMCS\Billing\Pricing
{
    protected $table = "tblpricing";
    public $timestamps = false;
    protected $columnMap = array("currencyId" => "currency", "year1" => "msetupfee", "year2" => "qsetupfee", "year3" => "ssetupfee", "year4" => "asetupfee", "year5" => "bsetupfee", "year6" => "monthly", "year7" => "quarterly", "year8" => "semiannually", "year9" => "annually", "year10" => "biennially", "clientGroupId" => "tsetupfee");
    protected $hidden = array("triennially");
    protected $acceptedTypesForScope = array("register", "renew", "transfer");
    protected $fillable = array("type", "relid", "currency", "tsetupfee");
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("only_domain_pricing", function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->whereIn("type", array("domainregister", "domainrenew", "domaintransfer"))->orderBy("tblpricing.tsetupfee");
        });
    }
    public function scopeOfTldId(\Illuminate\Database\Eloquent\Builder $query, $tldId)
    {
        return $query->where("relid", $tldId);
    }
    public function scopeOfClientGroup(\Illuminate\Database\Eloquent\Builder $query, $clientGroupId)
    {
        return $query->where("tsetupfee", $clientGroupId);
    }
    public function scopeOfType(\Illuminate\Database\Eloquent\Builder $query, $type = "register")
    {
        if (!in_array($type, $this->acceptedTypesForScope)) {
            throw new \InvalidArgumentException("Invalid Type for Scope. Must be one of: " . implode(", ", $this->acceptedTypesForScope));
        }
        return $query->where("type", "domain" . $type);
    }
    public function scopeOfCurrencyId(\Illuminate\Database\Eloquent\Builder $query, $currencyId = 1)
    {
        return $query->where("currency", $currencyId);
    }
    public function extension()
    {
        return $this->belongsTo("WHMCS\\Domains\\Extension", "relid");
    }
    public function currencyModel()
    {
        return $this->hasOne("WHMCS\\Billing\\Currency", "id", "currency");
    }
}

?>