<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module;

class Addon extends AbstractModule
{
    protected $type = self::TYPE_ADDON;
    public function getActiveModules()
    {
        return \WHMCS\Database\Capsule::table("tbladdonmodules")->distinct("module")->pluck("module");
    }
    public function call($function, array $params = array())
    {
        $return = parent::call($function, $params);
        if (isset($return["jsonResponse"])) {
            $response = new \WHMCS\Http\JsonResponse();
            $response->setData($return["jsonResponse"]);
            $response->send();
            \WHMCS\Terminus::getInstance()->doExit();
        }
        return $return;
    }
    public function getAdminActivationForms($moduleName)
    {
        return array((new \WHMCS\View\Form())->setUriPrefixAdminBaseUrl("configaddonmods.php")->setMethod(\WHMCS\View\Form::METHOD_POST)->setParameters(array("token" => generate_token("plain"), "action" => "activate", "module" => $moduleName))->setSubmitLabel(\AdminLang::trans("global.activate")));
    }
    public function getAdminManagementForms($moduleName)
    {
        return array((new \WHMCS\View\Form())->setUriPrefixAdminBaseUrl("addonmodules.php")->setMethod(\WHMCS\View\Form::METHOD_GET)->setParameters(array("module" => $moduleName))->setSubmitLabel(\AdminLang::trans("apps.info.useApp")));
    }
}

?>