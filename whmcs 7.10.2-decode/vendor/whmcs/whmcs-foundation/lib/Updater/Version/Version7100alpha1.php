<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Updater\Version;

class Version7100alpha1 extends IncrementalVersion
{
    protected $updateActions = array("removeUnusedLegacyModules", "createKbImageTable", "createKbFileAssetSetting", "createSuggestedTldsSetting", "convertEmailMarketerData", "addWeeblyFreeProductAndAddon", "updateWeeblySettingsWithIncludeFree", "addMarketgooWelcomeEmail", "updateCreditCardExpiringSoon");
    protected function createKbImageTable()
    {
        (new \WHMCS\Knowledgebase\Image())->createTable();
        return $this;
    }
    protected function createKbFileAssetSetting()
    {
        $existingSetting = \WHMCS\File\Configuration\FileAssetSetting::where("asset_type", \WHMCS\File\FileAsset::TYPE_KB_IMAGES)->first();
        if (!$existingSetting) {
            $existingConfigs = array(\WHMCS\File\FileAsset::TYPE_DOWNLOADS, \WHMCS\File\FileAsset::TYPE_TICKET_ATTACHMENTS, \WHMCS\File\FileAsset::TYPE_CLIENT_FILES, \WHMCS\File\FileAsset::TYPE_EMAIL_ATTACHMENTS, \WHMCS\File\FileAsset::TYPE_EMAIL_TEMPLATE_ATTACHMENTS);
            $existingConfigSetting = null;
            foreach ($existingConfigs as $existingConfig) {
                $existingConfigSetting = \WHMCS\File\Configuration\FileAssetSetting::where("asset_type", $existingConfig)->first();
                if ($existingConfigSetting) {
                    break;
                }
            }
            $setting = new \WHMCS\File\Configuration\FileAssetSetting();
            $setting->asset_type = \WHMCS\File\FileAsset::TYPE_KB_IMAGES;
            $setting->storageconfiguration_id = $existingConfigSetting->configuration->id;
            $setting->migratetoconfiguration_id = null;
            $setting->save();
        }
        return $this;
    }
    public function createSuggestedTldsSetting()
    {
        if (!function_exists("getTLDList")) {
            include_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "domainfunctions.php";
        }
        $availableTlds = getTLDList("register");
        $domainLookupProvider = \WHMCS\Config\Setting::getValue("domainLookupProvider");
        $domainLookupRegistrar = \WHMCS\Config\Setting::getValue("domainLookupRegistrar");
        if ($domainLookupProvider == "Registrar" && !empty($domainLookupRegistrar) && (is_array($availableTlds) || !empty($availableTlds))) {
            $suggestedTldsSetting = \WHMCS\Domains\DomainLookup\Settings::firstOrNew(array("registrar" => \WHMCS\Config\Setting::getValue("domainLookupRegistrar"), "setting" => "suggestTlds"));
            $suggestedTldsSetting->value = implode(",", $availableTlds);
            $suggestedTldsSetting->save();
        }
        return $this;
    }
    public function updateCreditCardExpiringSoon()
    {
        $md5Value = "ddfb244834d4dfcae90148e1c75854b4";
        $template = \WHMCS\Mail\Template::master()->where("name", "Credit Card Expiring Soon")->where("language", "")->first();
        if ($template && md5($template->message) === $md5Value) {
            $template->message = str_replace("interuptions", "interruptions", $template->message);
            $template->save();
        }
        return $this;
    }
    protected function convertEmailMarketerData()
    {
        (new \WHMCS\Admin\Utilities\Tools\EmailMarketer())->createPivotTable();
        foreach (\WHMCS\Admin\Utilities\Tools\EmailMarketer::all() as $emailMarketer) {
            $settings = $emailMarketer->getRawAttribute("settings");
            if (substr($settings, 0, 1) === "a") {
                $settings = safe_unserialize($settings);
                $products = array();
                $addons = array();
                if (is_array($settings) && isset($settings["prodpids"]) && is_array($settings["prodpids"])) {
                    foreach ($settings["prodpids"] as $productId) {
                        $type = substr($productId, 0, 1);
                        $id = substr($productId, 1);
                        if ($type === "P") {
                            $products[] = $id;
                            $emailMarketer->products()->attach($id);
                        } else {
                            $addons[] = $id;
                            $emailMarketer->addons()->attach($id);
                        }
                    }
                    unset($settings["prodpids"]);
                    $settings["products"] = $products;
                    $settings["addons"] = $addons;
                }
            }
            if (is_array($settings)) {
                $emailMarketer->settings = $settings;
                if ($emailMarketer->isDirty()) {
                    $emailMarketer->save();
                }
            }
        }
        return $this;
    }
    protected function addWeeblyFreeProductAndAddon()
    {
        $defaultHiddenState = true;
        $weeblyService = \WHMCS\MarketConnect\Service::whereName("weebly")->first();
        if (!is_null($weeblyService)) {
            $defaultHiddenState = !(bool) $weeblyService->status;
        }
        $weeblyLite = \WHMCS\Product\Product::where("servertype", "marketconnect")->where("configoption1", "weebly_lite")->first();
        $weeblyFree = \WHMCS\Product\Product::where("servertype", "marketconnect")->where("configoption1", "weebly_free")->first();
        if (!is_null($weeblyLite) && is_null($weeblyFree)) {
            $newProduct = $weeblyLite->replicate();
            $newProduct->name = "Free";
            $newProduct->paymentType = "free";
            $newProduct->autoSetup = "order";
            $newProduct->moduleConfigOption1 = "weebly_free";
            $newProduct->isRetired = false;
            $newProduct->isHidden = $defaultHiddenState;
            $newProduct->save();
        }
        if (!is_null($weeblyLite)) {
            $weeblyLite->isRetired = true;
            $weeblyLite->isHidden = true;
            $weeblyLite->save();
        }
        $weeblyLiteAddon = \WHMCS\Config\Module\ModuleConfiguration::with("productAddon")->where("entity_type", "addon")->where("setting_name", "configoption1")->where("value", "weebly_lite")->get()->where("productAddon.module", "marketconnect")->first();
        $weeblyFreeAddon = \WHMCS\Config\Module\ModuleConfiguration::with("productAddon")->where("entity_type", "addon")->where("setting_name", "configoption1")->where("value", "weebly_free")->get()->where("productAddon.module", "marketconnect")->first();
        if (!is_null($weeblyLiteAddon) && is_null($weeblyFreeAddon)) {
            $liteProductAddon = $weeblyLiteAddon->productAddon;
            $newAddon = $liteProductAddon->replicate();
            $newAddon->name = "Weebly Website Builder - Free";
            $newAddon->billingCycle = "free";
            $newAddon->autoActivate = "order";
            $newAddon->retired = false;
            $newAddon->hidden = false;
            $newAddon->save();
            foreach ($liteProductAddon->moduleConfiguration()->get() as $config) {
                $newConfig = $config->replicate();
                $newConfig->entityId = $newAddon->id;
                $newConfig->value = str_replace("weebly_lite", "weebly_free", $newConfig->value);
                $newConfig->save();
            }
        }
        if (!is_null($weeblyLiteAddon)) {
            $liteProductAddon = $weeblyLiteAddon->productAddon;
            $liteProductAddon->isRetired = true;
            $liteProductAddon->isHidden = true;
            $liteProductAddon->save();
        }
        return $this;
    }
    protected function updateWeeblySettingsWithIncludeFree()
    {
        $weeblyService = \WHMCS\MarketConnect\Service::whereName("weebly")->first();
        if ($weeblyService) {
            $settings = $weeblyService->settings;
            if (!array_key_exists("include-weebly-free-by-default", $settings)) {
                $settings["include-weebly-free-by-default"] = true;
                $weeblyService->settings = $settings;
                $weeblyService->save();
            }
        }
        return $this;
    }
    protected function addMarketgooWelcomeEmail()
    {
        $existing = \WHMCS\Mail\Template::master()->where("name", "Marketgoo Welcome Email")->first();
        if (!$existing) {
            $email = new \WHMCS\Mail\Template();
            $email->type = "product";
            $email->name = "Marketgoo Welcome Email";
            $email->subject = "Getting Started with Marketgoo";
            $email->language = "";
            $email->plaintext = false;
            $email->disabled = false;
            $email->custom = false;
            $email->message = "<p>Hi {\$client_first_name},</p>\n<p>Thank you for your purchase. Your website is now being analyzed by marketgoo and you will shortly receive an email with the next steps to improve your search engine ranking.</p>\n<p>To login and get started straight away, or check your SEO progress at any time, simply login to our client area and follow the link to access the Marketgoo dashboard.</p>\n<p><a href=\"{\$whmcs_url}clientarea.php\">{\$whmcs_url}clientarea.php</a></p>\n<p>If you have any questions or need help, please contact us by opening a <a href=\"{\$whmcs_url}submitticket.php\">support ticket</a></p>\n<p>{\$signature}</p>";
            $email->save();
        }
    }
    public function getUnusedLegacyModules()
    {
        return array("gateways" => array("kuveytturk"));
    }
    public function removeUnusedLegacyModules()
    {
        (new \WHMCS\Module\LegacyModuleCleanup())->removeModulesIfInstalledAndUnused($this->getUnusedLegacyModules());
        return $this;
    }
    public function getFeatureHighlights()
    {
        $utmString = "?utm_source=in-product&utm_medium=whatsnew710";
        return array(new \WHMCS\Notification\FeatureHighlight("TLD & Pricing <span>Sync</span>", "Automatically import and configure extensions from your domain registrar", null, "tld-import-sync.png", " Now you can import TLDs for faster initial setup, sync at any time to add new TLDs, and automatically set pricing based on a desired margin level. Supported for <strong>Enom</strong> and <strong>all Logicboxes</strong> registrar modules at launch.", "https://docs.whmcs.com/Registrar_TLD_Sync" . $utmString, "Learn More", routePath("admin-utilities-tools-tld-import-step-one"), "Try it now"), new \WHMCS\Notification\FeatureHighlight("<span>Marketgoo</span> from", "Easy-to-use self-service SEO Tools", "marketconnect.png", "marketgoo.png", "Give your customers the tools they need to grow with easy-to-use self-service SEO Tools that helps your customers be more successful and enable them to stay your customers for longer.", "marketconnect.php?learnmore=marketgoo", "Learn More", "marketconnect.php?activate=marketgoo", "Start selling"), new \WHMCS\Notification\FeatureHighlight("Additional Cron <span>Task Reporting</span>", "Get better insights into automated activity", null, "cron-reporting.png", "Get access to more detailed information about the daily actions performed by WHMCS including info such as who invoice reminders were sent to, which domains have been suspended, which credit card charges failed, and more.", "https://docs.whmcs.com/Automation_Status" . $utmString, "Learn More", "automationstatus.php", "Go to Automation Status"), new \WHMCS\Notification\FeatureHighlight("Weebly <span>Free Plan</span>", "Free site builder for your customers", null, "weebly.png", "Building a website doesn't have to be difficult. Give your customers access to the Weebly site builder - now available <strong>at no cost</strong> - to help them get online more quickly and easily.", "https://docs.whmcs.com/Weebly_via_WHMCS_MarketConnect" . $utmString, "Learn More", "marketconnect.php?learnmore=weebly", "Start offering Weebly"), new \WHMCS\Notification\FeatureHighlight("Client <span>Email Preferences</span>", "Giving clients more control over notifications", null, "email-prefs.png", "Allow customers to choose the types of emails they wish to receive, enabling them to reduce noise and make better use of contacts and sub-accounts.", "https://docs.whmcs.com/Email_Preferences" . $utmString, "Learn More"), new \WHMCS\Notification\FeatureHighlight("Knowledgebase Image <span>Uploads</span>", "Upload images directly via your browser", null, "kb-uploads.png", "It's now easier than ever to create rich and engaging knowledgebase articles with image upload functionality built directly into the article editor experience.", "https://docs.whmcs.com/Knowledgebase" . $utmString, "Learn More", "supportkb.php", "Try it now"));
    }
}

?>