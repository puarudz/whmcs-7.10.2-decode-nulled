<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Installer\Cli;

class Application extends AbstractApplication
{
    protected $preflightCount = 1;
    public function status()
    {
        $installer = $this->getInstaller();
        $padding = $this->getCli()->padding(37);
        $padding->label("Database schema version")->result($installer->getVersion()->getCanonical());
        $padding->label("Deployed WHMCS application version")->result($installer->getLatestVersion()->getCanonical());
        return $this;
    }
    public function preflightCheckOutput($msg)
    {
        $this->getCli()->inline(str_pad($this->preflightCount . ". " . $msg . " ", 60, ".", STR_PAD_RIGHT) . " ");
        $this->preflightCount++;
        return $this->getCli();
    }
    protected function preInstallCheck()
    {
        $cli = $this->getCli();
        $cli->comment("** Preflight Checks **");
        $this->preflightCheckOutput("Attempting to load configuration file");
        $config = new \WHMCS\Config\Application();
        $config->loadConfigFile(\WHMCS\Config\Application::WHMCS_DEFAULT_CONFIG_FILE);
        if (!$config->isConfigFileLoaded()) {
            $cli->error("FAILED")->br();
            throw new \WHMCS\Exception("Configuration file not found at '" . ROOTDIR . DIRECTORY_SEPARATOR . \WHMCS\Config\Application::WHMCS_DEFAULT_CONFIG_FILE . "'" . "\n" . "Installation requires a valid configuration file in the root WHMCS directory.");
        }
        $cli->green("Ok");
        $this->preflightCheckOutput("Attempting to connect to database");
        try {
            \DI::make("db");
        } catch (\WHMCS\Exception $e) {
            $cli->error("FAILED")->br();
            throw new \WHMCS\Exception("Database connection failed: " . $e->getMessage());
        }
        $cli->green("Ok");
        $this->preflightCheckOutput("Validating database for install");
        $installer = $this->getInstaller();
        try {
            $installer->checkIfInstalled(true);
            if ($installer->isInstalled()) {
                throw new \WHMCS\Exception("Existing WHMCS installation found in database.");
            }
            if (!$installer->getDatabase()) {
                throw new \WHMCS\Exception("Unable to connect to database.");
            }
            if (\DI::make("db")->isSqlStrictMode()) {
                throw new \WHMCS\Exception("MySQL Strict Mode is enabled.");
            }
        } catch (\Exception $e) {
            $cli->error("FAILED")->br();
            throw $e;
        }
        $cli->green("Ok")->br()->green("All checks passed successfully. Ready to Install.");
        return $this;
    }
    protected function createAdminUser(array $admin = array())
    {
        $installer = $this->getInstaller();
        if (!empty($admin["username"])) {
            $username = $admin["username"];
        } else {
            $username = "Admin";
        }
        if (!empty($admin["password"])) {
            $password = $admin["password"];
        } else {
            $password = generateFriendlyPassword();
        }
        if (!empty($admin["firstname"])) {
            $firstname = $admin["firstname"];
        } else {
            $firstname = "Primary";
        }
        if (!empty($admin["lastname"])) {
            $lastname = $admin["lastname"];
        } else {
            $lastname = "User";
        }
        if (!empty($admin["email"])) {
            $email = $admin["email"];
        } else {
            $email = "yourname@example.com";
        }
        $installer->createInitialAdminUser($username, $firstname, $lastname, $password, $email);
        return array($username, $password);
    }
    protected function outputAdminCreatedMessage($username, $password)
    {
        $this->getCli()->br()->out("A primary admin user account has been created with the following credentials:")->br()->out("Username: " . $username)->out("Password: " . $password)->br();
        return $this;
    }
    public function sanitizeConfig(array $config = array())
    {
        $validKeys = array("configuration", "settings", "localStorage", "admin");
        return array_intersect_key($config, array_flip($validKeys));
    }
    protected function createConfigurationFile(array $config)
    {
        $cli = $this->getCli();
        $file = ROOTDIR . DIRECTORY_SEPARATOR . \WHMCS\Config\Application::WHMCS_DEFAULT_CONFIG_FILE;
        if (file_exists($file) || is_link($file)) {
            $cli->error("FAILED")->br();
            throw new \WHMCS\Exception("Install with configuration requested but configuration file found at '" . $file . "'\n");
        }
        if (!touch($file)) {
            $cli->error("FAILED")->br();
            throw new \WHMCS\Exception("Could not create configuration file at '" . $file . "'\n");
        }
        $cli->comment("** Creating Configuration File **");
        $cli->out($file)->br();
        $configInstance = new \WHMCS\Config\Application();
        $configInstance->write($config, $file);
    }
    public function install(array $config = array())
    {
        $cli = $this->getCli();
        $cli->br();
        try {
            $settings = $configurationVariables = $localStorage = $admin = array();
            if (isset($config["configuration"]) && is_array($config["configuration"])) {
                $configurationVariables = $config["configuration"];
            }
            if (isset($config["settings"]) && is_array($config["settings"])) {
                $settings = $config["settings"];
            }
            if (isset($config["localStorage"]) && is_array($config["localStorage"])) {
                $localStorage = $config["localStorage"];
            }
            if (isset($config["admin"]) && is_array($config["admin"])) {
                $admin = $config["admin"];
            }
            if (!empty($configurationVariables)) {
                $this->createConfigurationFile($config["configuration"]);
            }
            $this->preInstallCheck();
            $installer = $this->getInstaller();
            if (!$cli->arguments->defined("non-interactive")) {
                $input = $cli->confirm("Are you sure you wish to continue?");
                if (!$input->confirmed()) {
                    throw new \WHMCS\Exception\Installer\UserBail("Installation aborted per request.");
                }
            }
            $cli->br()->comment("** Beginning Installation **")->out("This may take a few minutes. Please Wait...");
            $progressBar = $this->addProgressBar(3);
            $progressBar->advance(0, "Seeding Database");
            \Log::debug("Seeding Database");
            $installer->seedDatabase();
            $progressBar->advance(1, "Creating Initial Admin User");
            \Log::debug("Creating Initial Admin User");
            $userDetails = $this->createAdminUser($admin);
            $progressBar->advance(1, "Applying Non-Seed Changes");
            \Log::debug("Applying Non-Seed Changes");
            $installer->performNonSeedIncrementalChange($settings, $localStorage);
            $progressBar->advance(1, "<green>Install Completed Successfully!</green>");
            call_user_func_array(array($this, "outputAdminCreatedMessage"), $userDetails);
        } catch (\WHMCS\Exception\Installer\UserBail $e) {
            $cli->br()->bold($e->getMessage());
        }
        return $this;
    }
    public function upgrade()
    {
        $this->addProgressBar();
        $cli = $this->getCli();
        $installer = $this->getInstaller();
        $dbVersion = $installer->getVersion()->getCanonical();
        $filesVersion = $installer->getLatestVersion()->getCanonical();
        $cli->out("");
        $this->status();
        $cli->out("");
        if ($installer->isUpToDate()) {
            $cli->comment("WHMCS is up to date!");
        } else {
            try {
                if (!$cli->arguments->defined("non-interactive")) {
                    $input = $cli->confirm(sprintf("Are you sure you which to upgrade from %s to %s?", $dbVersion, $filesVersion));
                    if (!$input->confirmed()) {
                        throw new \WHMCS\Exception\Installer\UserBail("Upgrade aborted per request.");
                    }
                    $input = $cli->confirm("Have you backed up your database?");
                    if (!$input->confirmed()) {
                        throw new \WHMCS\Exception\Installer\UserBail("Please backup your database and run this program again.");
                    }
                    $cli->out("");
                }
                $cli->comment("** Beginning Upgrade **")->out("This may take a few minutes. Please Wait...");
                $cli->out("");
                try {
                    $installer->runUpgrades();
                } catch (\WHMCS\Exception $e) {
                    throw new \WHMCS\Exception\Fatal("Applying database upgrade failed: " . $e->getMessage());
                }
                \Log::debug("Applying Updates Done");
                $installer->checkIfInstalled();
                $cli->out("");
                $this->status();
            } catch (\WHMCS\Exception\Installer\UserBail $e) {
                $this->status();
                $cli->comment($e->getMessage());
            }
        }
        return $this;
    }
}

?>