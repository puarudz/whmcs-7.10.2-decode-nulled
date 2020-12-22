<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Utilities\Tools\EmailMarketer;

class Controller
{
    public function manage(\WHMCS\Http\Message\ServerRequest $request)
    {
        $id = $request->get("id", 0);
        $ruleName = "";
        $type = "client";
        $marketing = $disabled = false;
        $clientDays = $minimumServices = $maximumServices = "";
        $clientEmailTemplate = $productEmailTemplate = $numberOfDays = "";
        $selectedProducts = $selectedAddons = $withoutProducts = array();
        $withoutAddons = $selectedStatuses = $selectedCycles = array();
        $daysType = "after_order";
        $clientEmailTemplates = \WHMCS\Mail\Template::master()->where("type", "general")->get();
        $productEmailTemplates = \WHMCS\Mail\Template::master()->where("type", "product")->get();
        $products = \WHMCS\Product\Product::with("productGroup")->get();
        $addons = \WHMCS\Product\Addon::all();
        try {
            if ($id) {
                $rule = \WHMCS\Admin\Utilities\Tools\EmailMarketer::findOrFail($id);
                $id = $rule->id;
                $ruleName = $rule->name;
                $type = $rule->type;
                $marketing = $rule->marketing;
                $disabled = $rule->disabled;
                $settings = $rule->settings;
                $clientDays = $settings["clientnumdays"];
                $minimumServices = $settings["clientsminactive"];
                $maximumServices = $settings["clientsmaxactive"];
                $clientEmailTemplate = $settings["clientemailtpl"];
                $productEmailTemplate = $settings["prodemailtpl"];
                $selectedStatuses = $settings["prodstatus"];
                $selectedCycles = $settings["product_cycle"];
                $numberOfDays = $settings["prodnumdays"];
                $daysType = $settings["prodfiltertype"];
                $withoutAddons = $settings["prodexcludeaid"];
                if (!is_array($selectedStatuses)) {
                    $selectedStatuses = array();
                }
                if (!is_array($selectedCycles)) {
                    $selectedCycles = array();
                }
                if (!is_array($withoutAddons)) {
                    $withoutAddons = array();
                }
                $withoutProducts = $settings["prodexcludepid"];
                if (!is_array($withoutProducts)) {
                    $withoutProducts = array();
                }
                $selectedProducts = $settings["products"];
                if (!is_array($selectedProducts)) {
                    $selectedProducts = array();
                }
                $selectedAddons = $settings["addons"];
                if (!is_array($selectedAddons)) {
                    $selectedAddons = array();
                }
            }
            $response = array("body" => view("admin.utilities.email-marketer.manage", array("id" => $id, "ruleName" => $ruleName, "type" => $type, "marketing" => $marketing, "disabled" => $disabled, "clientDays" => $clientDays, "minimumServices" => $minimumServices, "maximumServices" => $maximumServices, "clientEmailTemplate" => $clientEmailTemplate, "productEmailTemplate" => $productEmailTemplate, "numberOfDays" => $numberOfDays, "selectedProducts" => $selectedProducts, "selectedAddons" => $selectedAddons, "withoutProducts" => $withoutProducts, "withoutAddons" => $withoutAddons, "selectedStatuses" => $selectedStatuses, "selectedCycles" => $selectedCycles, "daysType" => $daysType, "clientEmailTemplates" => $clientEmailTemplates, "productEmailTemplates" => $productEmailTemplates, "products" => $products, "addons" => $addons, "cycles" => (new \WHMCS\Billing\Cycles())->getPublicBillingCycles())));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $response = array("error" => true, "dismiss" => true, "errorMsg" => \AdminLang::trans("utilities.emailMarketer.notFound"));
        } catch (\Exception $e) {
            $response = array("error" => true, "dismiss" => true, "errorMsg" => $e->getMessage());
        }
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
    public function save(\WHMCS\Http\Message\ServerRequest $request)
    {
        try {
            $id = $request->get("id", 0);
            $rule = null;
            $success = "utilities.emailMarketer.added";
            if ($id) {
                $rule = \WHMCS\Admin\Utilities\Tools\EmailMarketer::findOrFail($id);
                $success = "utilities.emailMarketer.updated";
            }
            if (!$rule) {
                $rule = new \WHMCS\Admin\Utilities\Tools\EmailMarketer();
            }
            $rule->name = $request->get("name");
            $type = $request->get("type", "client");
            if (!in_array($type, array("client", "product"))) {
                $type = "client";
            }
            $rule->type = $type;
            $rule->marketing = (bool) (int) $request->get("marketing");
            $rule->disabled = (bool) (int) $request->get("disabled");
            $settings = array("clientnumdays" => $request->get("client_days", ""), "clientsminactive" => $request->get("min_services", ""), "clientsmaxactive" => $request->get("max_services", ""), "clientemailtpl" => $request->get("email_template_client"), "products" => $request->get("products", array()), "addons" => $request->get("addons", array()), "prodstatus" => $request->get("product_status", array()), "product_cycle" => $request->get("product_cycle", array()), "prodnumdays" => $request->get("number_of_days", ""), "prodfiltertype" => $request->get("days_type"), "prodexcludepid" => $request->get("without_product", array()), "prodexcludeaid" => $request->get("without_addon", array()), "prodemailtpl" => $request->get("email_template_product"));
            $rule->settings = $settings;
            $rule->save();
            \WHMCS\Database\Capsule::table("tblemailmarketer_related_pivot")->where("task_id", $rule->id)->delete();
            foreach ($settings["products"] as $product) {
                $rule->products()->attach($product);
            }
            foreach ($settings["addons"] as $addon) {
                $rule->addons()->attach($addon);
            }
            $response = array("dismiss" => true, "success" => true, "reloadPage" => true, "successMsg" => \AdminLang::trans($success), "successMsgTitle" => "");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $response = array("error" => true, "errorMsg" => \AdminLang::trans("utilities.emailMarketer.notFound"));
        } catch (\Exception $e) {
            $response = array("error" => true, "errorMsg" => $e->getMessage());
        }
        return new \WHMCS\Http\Message\JsonResponse($response);
    }
}

?>