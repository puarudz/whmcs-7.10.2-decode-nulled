<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\MarketConnect;

class SitelockController
{
    public function index(\WHMCS\Http\Message\ServerRequest $request)
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        if (!$isAdminPreview) {
            $service = Service::where("name", "sitelock")->first();
            if (is_null($service) || !$service->status) {
                return new \Zend\Diactoros\Response\RedirectResponse("index.php");
            }
        }
        $ca = new Output\ClientArea();
        $ca->setPageTitle(\Lang::trans("store.sitelock.title"));
        $ca->addToBreadCrumb("index.php", \Lang::trans("globalsystemname"));
        $ca->addToBreadCrumb(routePath("store"), \Lang::trans("navStore"));
        $ca->addToBreadCrumb(routePath("store-sitelock-index"), \Lang::trans("store.sitelock.title"));
        $ca->initPage();
        $sessionCurrency = \WHMCS\Session::get("currency");
        $currency = getCurrency($ca->getUserId(), $sessionCurrency);
        $ca->assign("activeCurrency", $currency);
        $sitelockPromoHelper = MarketConnect::factoryPromotionalHelper("sitelock");
        if ($isAdminPreview) {
            $plans = (new ServicesFeed())->getEmulationOfConfiguredProducts("sitelock");
        } else {
            $plans = \WHMCS\Product\Product::sitelock()->visible()->get();
        }
        $litePlan = $plans->where("configoption1", "sitelock_lite")->first();
        $emergencyPlan = $plans->where("configoption1", "sitelock_emergency")->first();
        if ($emergencyPlan) {
            $emergencyPlan->pricing($currency);
        }
        foreach ($plans as $key => $plan) {
            if (in_array($plan->configoption1, array("sitelock_lite", "sitelock_emergency"))) {
                unset($plans[$key]);
                continue;
            }
            $plan->features = $sitelockPromoHelper->getPlanFeatures($plan->configoption1);
            $plan->pricing($currency);
        }
        $ca->assign("plans", $plans);
        $ca->assign("litePlan", $litePlan);
        $ca->assign("emergencyPlan", $emergencyPlan);
        $ca->assign("inPreview", $isAdminPreview);
        $ca->setTemplate("store/sitelock/index");
        $ca->skipMainBodyContainer();
        return $ca;
    }
}

?>