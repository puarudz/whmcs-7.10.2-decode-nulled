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

class Version600alpha1 extends IncrementalVersion
{
    protected $updateActions = array("removeDuplicateSettings", "convertMailTemplateBooleanColumns", "convertClientUnixTimestampColumns", "convertClientBooleanColumns", "convertDomainBooleanColumns", "convertProductBooleanColumns", "convertProductGroupBooleanColumns", "convertDownloadBooleanColumns", "convertDownloadCategoryBooleanColumns", "migrateProductDownloadIdsToItsTable", "migrateProductUpgradeIdsToItsTable", "convertServiceBooleanColumns", "convertAnnouncementBooleanColumns", "updateAdminUserForAutoReleaseModule", "createServiceUnsuspendedEmailTemplate", "addManualUpgradeRequiredEmailTemplate", "convertNoMD5Passwords", "migrateDiscontinuedOrderFormTemplates", "migrateDiscontinuedAdminOriginalTemplate", "convertContactUnixTimestampColumns");
    public function __construct(\WHMCS\Version\SemanticVersion $version)
    {
        parent::__construct($version);
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "WHMCS" . DIRECTORY_SEPARATOR . "Email";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "Smarty";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "WHMCS" . DIRECTORY_SEPARATOR . "Smarty" . DIRECTORY_SEPARATOR . "Compiler.php";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "phpseclib";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "ircmaxell";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "dbconnect.php";
    }
    protected function removeDuplicateSettings()
    {
        $distinctSettingNames = \Illuminate\Database\Capsule\Manager::table("tblconfiguration")->select("setting", "value")->distinct("setting")->get();
        foreach ($distinctSettingNames as $distinctSetting) {
            $settings = \Illuminate\Database\Capsule\Manager::table("tblconfiguration")->where("setting", "=", $distinctSetting->setting)->get();
            for ($i = 0; $i < count($settings) - 1; $i++) {
                \Illuminate\Database\Capsule\Manager::table("tblconfiguration")->where("setting", "=", $distinctSetting->setting)->where("value", "=", $settings[$i]->value)->delete();
                if (\Illuminate\Database\Capsule\Manager::table("tblconfiguration")->where("setting", "=", $distinctSetting->setting)->count() == 0) {
                    \Illuminate\Database\Capsule\Manager::table("tblconfiguration")->insert(array("setting" => $distinctSetting->setting, "value" => $settings[$i]->value, "created_at" => $settings[$i]->created_at, "updated_at" => $settings[$i]->updated_at));
                    break;
                }
            }
        }
        return $this;
    }
    protected function convertMailTemplateBooleanColumns()
    {
        $columns = array("disabled", "custom", "plaintext");
        foreach ($columns as $column) {
            \WHMCS\Mail\Template::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function convertClientUnixTimestampColumns()
    {
        $columns = array("pwresetexpiry");
        foreach ($columns as $column) {
            \WHMCS\User\Client::convertUnixTimestampIntegerToTimestampColumn($column);
        }
        return $this;
    }
    protected function convertClientBooleanColumns()
    {
        $columns = array("taxexempt", "latefeeoveride", "overideduenotices", "separateinvoices", "disableautocc");
        foreach ($columns as $column) {
            \WHMCS\User\Client::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function convertDomainBooleanColumns()
    {
        $columns = array("dnsmanagement", "emailforwarding", "idprotection", "donotrenew", "synced");
        foreach ($columns as $column) {
            \WHMCS\Domain\Domain::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function convertProductBooleanColumns()
    {
        $columns = array("hidden", "showdomainoptions", "stockcontrol", "proratabilling", "configoptionsupgrade", "tax", "affiliateonetime", "retired");
        foreach ($columns as $column) {
            \WHMCS\Product\Product::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function convertProductGroupBooleanColumns()
    {
        $columns = array("hidden");
        foreach ($columns as $column) {
            \WHMCS\Product\Group::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function convertDownloadBooleanColumns()
    {
        $columns = array("clientsonly", "hidden", "productdownload");
        foreach ($columns as $column) {
            \WHMCS\Download\Download::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function convertDownloadCategoryBooleanColumns()
    {
        $columns = array("hidden");
        foreach ($columns as $column) {
            \WHMCS\Download\Category::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function migrateProductDownloadIdsToItsTable()
    {
        $productModel = new \WHMCS\Product\Product();
        if (\Illuminate\Database\Capsule\Manager::schema()->hasColumn($productModel->getTable(), "downloads")) {
            $productsWithDownloads = \WHMCS\Product\Product::where("downloads", "!=", "")->where("downloads", "!=", "N;")->get();
            foreach ($productsWithDownloads as $product) {
                $downloads = safe_unserialize($product->downloads);
                if (!is_array($downloads)) {
                    continue;
                }
                foreach ($downloads as $downloadId) {
                    \Illuminate\Database\Capsule\Manager::table("tblproduct_downloads")->insert(array("product_id" => $product->id, "download_id" => $downloadId));
                }
            }
            \Illuminate\Database\Capsule\Manager::schema()->table($productModel->getTable(), function ($table) {
                $table->dropColumn("downloads");
            });
        }
        return $this;
    }
    protected function migrateProductUpgradeIdsToItsTable()
    {
        $productModel = new \WHMCS\Product\Product();
        if (\Illuminate\Database\Capsule\Manager::schema()->hasColumn($productModel->getTable(), "upgradepackages")) {
            $productsWithUpgrades = \WHMCS\Product\Product::where("upgradepackages", "!=", "")->where("upgradepackages", "!=", "N;")->get();
            foreach ($productsWithUpgrades as $product) {
                $upgrades = safe_unserialize($product->upgradepackages);
                if (!is_array($upgrades)) {
                    continue;
                }
                foreach ($upgrades as $upgradeProductId) {
                    \Illuminate\Database\Capsule\Manager::table("tblproduct_upgrade_products")->insert(array("product_id" => $product->id, "upgrade_product_id" => $upgradeProductId));
                }
            }
            \Illuminate\Database\Capsule\Manager::schema()->table($productModel->getTable(), function ($table) {
                $table->dropColumn("upgradepackages");
            });
        }
        return $this;
    }
    protected function convertServiceBooleanColumns()
    {
        $columns = array("overideautosuspend");
        foreach ($columns as $column) {
            \WHMCS\Service\Service::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function convertAnnouncementBooleanColumns()
    {
        $columns = array("published");
        foreach ($columns as $column) {
            \WHMCS\Announcement\Announcement::convertBooleanColumn($column);
        }
        return $this;
    }
    protected function updateAdminUserForAutoReleaseModule()
    {
        $admin = \WHMCS\User\Admin::where("disabled", "=", false)->first(array("id", "username", "firstname", "lastname"));
        $adminToSave = (string) $admin->id . "|" . $admin->firstname . " " . $admin->lastname . " (" . $admin->username . ")";
        $products = \Illuminate\Database\Capsule\Manager::table("tblproducts")->where("servertype", "=", "autorelease")->get();
        foreach ($products as $product) {
            \Illuminate\Database\Capsule\Manager::table("tblproducts")->where("id", "=", $product->id)->update(array("configoption7" => $adminToSave));
        }
        return $this;
    }
    protected function createServiceUnsuspendedEmailTemplate()
    {
        $message = "<p>Dear {\$client_name},</p>" . PHP_EOL . "<p>This is a notification that your service has now been unsuspended." . " The details of this unsuspension are below:</p>" . PHP_EOL . "<p>Product/Service: {\$service_product_name}<br />" . "{if \$service_domain}Domain: {\$service_domain}<br />" . "{/if}Amount: {\$service_recurring_amount}<br />" . "Due Date: {\$service_next_due_date}</p>" . PHP_EOL . "<p>{\$signature}</p>";
        $template = new \WHMCS\Mail\Template();
        $template->type = "product";
        $template->name = "Service Unsuspension Notification";
        $template->subject = "Service Unsuspension Notification";
        $template->message = $message;
        $template->save();
        return $this;
    }
    protected function addManualUpgradeRequiredEmailTemplate()
    {
        $existingEmail = \WHMCS\Mail\Template::where("name", "=", "Manual Upgrade Required")->count();
        if (!$existingEmail) {
            $emailMessage = "<p>An upgrade order has received its payment, " . "but does not support automatic upgrades and requires manually processing.</p>" . PHP_EOL . "<p>Client ID: {\$client_id}<br />Service ID: {\$service_id}<br />Order ID: {\$order_id}</p>" . PHP_EOL . "<p>{if \$upgrade_type eq 'package'}New Package ID: {\$new_package_id}<br />" . "Existing Billing Cycle: {\$billing_cycle}<br />New Billing Cycle: {\$new_billing_cycle}" . "{else}Configurable Option: {\$config_id}<br />Option Type: {\$option_type}<br />" . "Current Value: {\$current_value}<br />New Value: {\$new_value}{/if}</p>" . PHP_EOL . "<p><a href=\"{\$whmcs_admin_url}orders.php?action=view&id={\$order_id}\">" . PHP_EOL . "{\$whmcs_admin_url}orders.php?action=view&id={\$order_id}</a></p>";
            $email = new \WHMCS\Mail\Template();
            $email->name = "Manual Upgrade Required";
            $email->subject = "Manual Upgrade Required";
            $email->message = $emailMessage;
            $email->type = "admin";
            $email->custom = false;
            $email->plaintext = false;
            $email->save();
        }
        return $this;
    }
    protected function convertNoMD5Passwords()
    {
        $nomd5 = \WHMCS\Config\Setting::getValue("NOMD5");
        if (!empty($nomd5)) {
            require_once ROOTDIR . "/includes/functions.php";
            require_once ROOTDIR . "/includes/clientfunctions.php";
            foreach (\WHMCS\User\Client::all() as $client) {
                $client->password = generateClientPW(decrypt($client->password));
                $client->save();
            }
            $contacts = \Illuminate\Database\Capsule\Manager::table("tblcontacts")->get();
            foreach ($contacts as $contact) {
                $password = generateClientPW(decrypt($contact->password));
                \Illuminate\Database\Capsule\Manager::table("tblcontacts")->where("id", "=", $contact->id)->update(array("password" => $password));
            }
            try {
                \WHMCS\Config\Setting::findOrFail("NOMD5")->delete();
            } catch (\Exception $e) {
            }
        }
        return $this;
    }
    protected function migrateDiscontinuedOrderFormTemplates()
    {
        $discontinuedTemplates = array("ajaxcart" => "cart", "web20cart" => "boxes");
        foreach ($discontinuedTemplates as $discontinuedTemplate => $templateToMigrateTo) {
            \WHMCS\Product\Group::where("orderfrmtpl", "=", $discontinuedTemplate)->update(array("orderfrmtpl" => $templateToMigrateTo));
            if (\WHMCS\Config\Setting::getValue("OrderFormTemplate") == $discontinuedTemplate) {
                \WHMCS\Config\Setting::setValue("OrderFormTemplate", $templateToMigrateTo);
            }
        }
        return $this;
    }
    protected function migrateDiscontinuedAdminOriginalTemplate()
    {
        $admin = \WHMCS\User\Admin::where("template", "=", "original");
        $admin->getModel()->timestamps = false;
        $admin->update(array("template" => "blend"));
        return $this;
    }
    protected function convertContactUnixTimestampColumns()
    {
        $columns = array("pwresetexpiry");
        foreach ($columns as $column) {
            \WHMCS\User\Client\Contact::convertUnixTimestampIntegerToTimestampColumn($column);
        }
        return $this;
    }
}

?>