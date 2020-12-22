<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\User\Traits;

trait EmailPreferences
{
    public static $emailPreferencesDefaults = NULL;
    public function validateEmailPreferences(array $preferences)
    {
        if (!$preferences) {
            return NULL;
        }
        $preferenceKeys = array_filter(array_keys($preferences), function ($key) {
            return !in_array($key, \WHMCS\Mail\Emailer::CLIENT_EMAILS);
        });
        if ($preferenceKeys && 0 < count($preferenceKeys)) {
            $preferenceKeys = implode(", ", $preferenceKeys);
            $valid = implode(", ", \WHMCS\Mail\Emailer::CLIENT_EMAILS);
            throw new \InvalidArgumentException("Invalid Email Type: " . $preferenceKeys . ". Valid options are: " . $valid);
        }
        if ($this instanceof \WHMCS\User\Client) {
            if (\WHMCS\Config\Setting::getValue("DisableClientEmailPreferences")) {
                return NULL;
            }
            if (isset($preferences[\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN]) && !$preferences[\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN] && $this->getEmailPreference(\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN) && $this->contacts()->where(\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN . "emails", 1)->count() === 0) {
                throw new \WHMCS\Exception\Validation\Required("emailPreferences.domainClientRequired");
            }
        } else {
            if ($this instanceof \WHMCS\User\Client\Contact && isset($preferences[\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN]) && !$preferences[\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN] && $this->getEmailPreference(\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN) && !$this->client->getEmailPreference(\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN) && $this->client->contacts()->where(\WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN . "emails", 1)->where("id", "!=", $this->id)->count() === 0) {
                throw new \WHMCS\Exception\Validation\Required("emailPreferences.domainContactRequired");
            }
        }
    }
    public function getEmailPreferences()
    {
        if ($this instanceof \WHMCS\User\Client) {
            if (!$this->emailPreferences) {
                return self::$emailPreferencesDefaults;
            }
            return $this->emailPreferences;
        }
        return array(\WHMCS\Mail\Emailer::EMAIL_TYPE_GENERAL => $this->receivesGeneralEmails, \WHMCS\Mail\Emailer::EMAIL_TYPE_INVOICE => $this->receivesInvoiceEmails, \WHMCS\Mail\Emailer::EMAIL_TYPE_SUPPORT => $this->receivesSupportEmails, \WHMCS\Mail\Emailer::EMAIL_TYPE_PRODUCT => $this->receivesProductEmails, \WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN => $this->receivesDomainEmails, \WHMCS\Mail\Emailer::EMAIL_TYPE_AFFILIATE => $this->receivesAffiliateEmails);
    }
    public function getEmailPreference($type)
    {
        return $this->getEmailPreferences()[$type];
    }
    public function setEmailPreferences($preferences)
    {
        if (!count($preferences)) {
            return NULL;
        }
        $this->validateEmailPreferences($preferences);
        $storedPreferences = $this->getEmailPreferences();
        if ($this instanceof \WHMCS\User\Client) {
            if (\WHMCS\Config\Setting::getValue("DisableClientEmailPreferences")) {
                $preferences = self::$emailPreferencesDefaults;
            }
            $this->emailPreferences = array_merge($storedPreferences, $preferences);
        } else {
            if ($this instanceof \WHMCS\User\Client\Contact) {
                foreach ($preferences as $preference => $value) {
                    $var = $preference . "emails";
                    $this->{$var} = $value;
                }
            }
        }
    }
}

?>