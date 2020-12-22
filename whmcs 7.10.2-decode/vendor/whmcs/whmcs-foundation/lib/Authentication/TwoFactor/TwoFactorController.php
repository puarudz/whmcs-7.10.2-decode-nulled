<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Authentication\TwoFactor;

class TwoFactorController
{
    protected $inAdminArea = false;
    protected $userIdSessionVariableName = "uid";
    protected function isAdmin()
    {
        return $this->inAdminArea;
    }
    protected function getUserId()
    {
        return (int) \WHMCS\Session::get($this->userIdSessionVariableName);
    }
    protected function initTwoFactorObject()
    {
        $twofa = new \WHMCS\TwoFactorAuthentication();
        if ($this->isAdmin()) {
            if (!$twofa->isActiveAdmins()) {
                throw new \WHMCS\Exception("Two-Factor Authentication is not enabled.");
            }
            $twofa->setAdminID($this->getUserId());
        } else {
            if (!$twofa->isActiveClients()) {
                throw new \WHMCS\Exception("Two-Factor Authentication is not enabled.");
            }
            $twofa->setClientID($this->getUserId());
        }
        return $twofa;
    }
    public function enable(\WHMCS\Http\Message\ServerRequest $request)
    {
        $langClass = "Lang";
        if ($this->isAdmin()) {
            $langClass = "AdminLang";
        }
        $twofa = $this->initTwoFactorObject();
        $modules = array();
        $descriptions = array();
        $moduleInterface = new \WHMCS\Module\Security();
        foreach ($twofa->getAvailableModules() as $module) {
            $moduleInterface->load($module);
            $configuration = $moduleInterface->call("config");
            $friendlyName = $langClass::trans("twoFactor." . $module . ".friendlyName");
            if ($friendlyName == "twoFactor." . $module . ".friendlyName") {
                $friendlyName = isset($configuration["FriendlyName"]["Value"]) ? $configuration["FriendlyName"]["Value"] : ucfirst($module);
            }
            $description = $langClass::trans("twoFactor." . $module . ".description");
            if ($description == "twoFactor." . $module . ".description") {
                $description = isset($configuration["ShortDescription"]["Value"]) ? $configuration["ShortDescription"]["Value"] : "No description available";
            }
            $modules[$module] = $friendlyName;
            $descriptions[$module] = $description;
        }
        $response = array("body" => view("authentication.two-factor.enable-choose", array("isAdmin" => $this->isAdmin(), "modules" => $modules, "descriptions" => $descriptions, "webRoot" => \WHMCS\Utility\Environment\WebHelper::getBaseUrl(), "twoFactorEnforced" => $request->get("enforce"))));
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
    public function configure(\WHMCS\Http\Message\ServerRequest $request, $verifyError = "")
    {
        $module = $request->request()->get("module");
        $twofa = $this->initTwoFactorObject();
        $modules = $twofa->getAvailableModules();
        if (!in_array($module, $modules)) {
            throw new \WHMCS\Exception("Invalid module name");
        }
        $output = $twofa->moduleCall("activate", $module, array("verifyError" => $verifyError));
        if (is_null($output)) {
            return $this->verify($request);
        }
        $response = array("body" => view("authentication.two-factor.enable-configure", array("isAdmin" => $this->isAdmin(), "module" => $module, "twoFactorConfigurationOutput" => $output)));
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
    public function qrCode(\WHMCS\Http\Message\ServerRequest $request)
    {
        $module = $request->getAttribute("module");
        $twofa = $this->initTwoFactorObject();
        $modules = $twofa->getAvailableModules();
        if (!in_array($module, $modules)) {
            throw new \WHMCS\Exception("Invalid module name");
        }
        $twofa->moduleCall("getqrcode", $module);
    }
    public function verify(\WHMCS\Http\Message\ServerRequest $request)
    {
        $module = $request->request()->get("module");
        $twofa = $this->initTwoFactorObject();
        $modules = $twofa->getAvailableModules();
        if (!in_array($module, $modules)) {
            throw new \WHMCS\Exception("Invalid module name");
        }
        try {
            $response = $twofa->moduleCall("activateverify", $module);
            $displayMsg = isset($response["msg"]) ? $response["msg"] : "";
            $settings = isset($response["settings"]) ? $response["settings"] : array();
            $backupCode = $twofa->activateUser($module, $settings);
            if (!$backupCode) {
                throw new \WHMCS\Exception(\Lang::trans("twofaactivationerror"));
            }
        } catch (\WHMCS\Exception $e) {
            return $this->configure($request, $e->getMessage());
        }
        $response = array("body" => view("authentication.two-factor.enable-complete", array("isAdmin" => $this->isAdmin(), "displayMsg" => $displayMsg, "backupCode" => $backupCode)));
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
    public function disable(\WHMCS\Http\Message\ServerRequest $request, $errorMsg = "")
    {
        $twofa = $this->initTwoFactorObject();
        $response = array("body" => view("authentication.two-factor.disable-confirm", array("isAdmin" => $this->isAdmin(), "errorMsg" => $errorMsg)));
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
    public function disableConfirm(\WHMCS\Http\Message\ServerRequest $request)
    {
        $inputVerifyPassword = $request->request()->get("pwverify");
        $twofa = $this->initTwoFactorObject();
        try {
            $twofa->validateAndDisableUser($inputVerifyPassword);
        } catch (\WHMCS\Exception $e) {
            $errorMsg = $e->getMessage();
            return $this->disable($request, $errorMsg);
        }
        $response = array("body" => view("authentication.two-factor.disable-complete", array("isAdmin" => $this->isAdmin())));
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
}

?>