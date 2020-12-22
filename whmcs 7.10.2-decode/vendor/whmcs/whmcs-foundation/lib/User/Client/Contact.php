<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\User\Client;

class Contact extends \WHMCS\Model\AbstractModel implements \WHMCS\User\Contracts\ContactInterface
{
    use \WHMCS\User\Traits\EmailPreferences;
    protected $table = "tblcontacts";
    protected $columnMap = array("clientId" => "userid", "isSubAccount" => "subaccount", "passwordHash" => "password", "receivesDomainEmails" => "domainemails", "receivesGeneralEmails" => "generalemails", "receivesInvoiceEmails" => "invoiceemails", "receivesProductEmails" => "productemails", "receivesSupportEmails" => "supportemails", "receivesAffiliateEmails" => "affiliateemails", "passwordResetKey" => "pwresetkey", "passwordResetKeyExpiryDate" => "pwresetexpiry");
    protected $dates = array("passwordResetKeyExpiryDate");
    protected $booleans = array("isSubAccount", "receivesDomainEmails", "receivesGeneralEmails", "receivesInvoiceEmails", "receivesProductEmails", "receivesSupportEmails", "receivesAffiliateEmails");
    protected $commaSeparated = array("permissions");
    protected $appends = array("fullName", "countryName");
    public static $allPermissions = array("profile", "contacts", "products", "manageproducts", "productsso", "domains", "managedomains", "invoices", "quotes", "tickets", "affiliates", "emails", "orders");
    protected $hidden = array("password", "pwresetkey", "pwresetexpiry");
    public function client()
    {
        return $this->belongsTo("WHMCS\\User\\Client", "userid");
    }
    public function remoteAccountLinks()
    {
        return $this->hasMany("WHMCS\\Authentication\\Remote\\AccountLink", "contact_id");
    }
    public function orders()
    {
        return $this->hasMany("WHMCS\\Order\\Order", "id", "orderid");
    }
    public function getFullNameAttribute()
    {
        return (string) $this->firstname . " " . $this->lastname;
    }
    public function getCountryNameAttribute()
    {
        static $countries = NULL;
        if (is_null($countries)) {
            $countries = new \WHMCS\Utility\Country();
        }
        return $countries->getName($this->country);
    }
    public function updateLastLogin(\WHMCS\Carbon $time = NULL, $ip = NULL, $host = NULL)
    {
        return $this->client->updateLastLogin($time, $ip, $host);
    }
    public function getLanguageAttribute()
    {
        return $this->client->language;
    }
    public function getTwoFactorAuthModuleAttribute()
    {
        return "";
    }
    public function tickets()
    {
        return $this->hasMany("WHMCS\\Support\\Ticket", "contactid");
    }
    public static function currentContactHasPermissionName($permissionName)
    {
        $sessionCid = \WHMCS\Session::get("cid");
        if ($sessionCid) {
            $contact = Contact::find($sessionCid);
            $contactPermissions = $contact->permissions;
            if (!in_array($permissionName, $contactPermissions)) {
                return false;
            }
        }
        return true;
    }
}

?>