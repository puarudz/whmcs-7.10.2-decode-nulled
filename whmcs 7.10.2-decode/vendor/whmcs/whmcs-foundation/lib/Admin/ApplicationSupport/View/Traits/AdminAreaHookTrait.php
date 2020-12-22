<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\ApplicationSupport\View\Traits;

trait AdminAreaHookTrait
{
    public function runHookAdminFooterOutput(array $hookVariables)
    {
        $hookResult = run_hook("AdminAreaFooterOutput", $hookVariables);
        $hookResult[] = view("admin.utilities.date.footer");
        return count($hookResult) ? implode("\n", $hookResult) : "";
    }
    public function runHookAdminHeaderOutput(array $hookVariables)
    {
        $hookResult = run_hook("AdminAreaHeaderOutput", $hookVariables);
        return count($hookResult) ? implode("\n", $hookResult) : "";
    }
    public function runHookAdminHeadOutput(array $hookVariables)
    {
        $hookResult = run_hook("AdminAreaHeadOutput", $hookVariables);
        return count($hookResult) ? implode("\n", $hookResult) : "";
    }
    public function runHookAdminAreaPage(array $hookVariables)
    {
        $hookResult = run_hook("AdminAreaPage", $hookVariables);
        foreach ($hookResult as $arr) {
            foreach ($arr as $k => $v) {
                $hookVariables[$k] = $v;
            }
        }
        return $hookVariables;
    }
}

?>