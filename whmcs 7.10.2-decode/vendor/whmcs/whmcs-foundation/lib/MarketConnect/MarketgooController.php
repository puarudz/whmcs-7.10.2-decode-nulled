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

class MarketgooController
{
    public function index(\WHMCS\Http\Message\ServerRequest $request)
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        if (!$isAdminPreview) {
            $service = Service::where("name", "marketgoo")->first();
            if (is_null($service) || !$service->status) {
                return new \WHMCS\ApplicationLink\Server\SingleSignOn\RedirectResponse("index.php");
            }
        }
        $ca = new Output\ClientArea();
        $ca->setPageTitle(\Lang::trans("store.marketgoo.title"));
        $ca->addToBreadCrumb("index.php", \Lang::trans("globalsystemname"));
        $ca->addToBreadCrumb(routePath("store"), \Lang::trans("navStore"));
        $ca->addToBreadCrumb(routePath("store-marketgoo-index"), \Lang::trans("store.marketgoo.title"));
        $ca->initPage();
        $marketgooPromoHelper = MarketConnect::factoryPromotionalHelper("marketgoo");
        if ($isAdminPreview) {
            $plans = (new ServicesFeed())->getEmulationOfConfiguredProducts("marketgoo");
        } else {
            $plans = \WHMCS\Product\Product::marketgoo()->visible()->orderBy("order")->get();
        }
        $sessionCurrency = \WHMCS\Session::get("currency");
        $currency = getCurrency($ca->getUserId(), $sessionCurrency);
        $ca->assign("activeCurrency", $currency);
        foreach ($plans as $key => $plan) {
            $plan->features = $marketgooPromoHelper->getPlanFeatures($plan->configoption1);
            if (!$isAdminPreview) {
                $pricing = $plan->pricing($currency);
                if (!$pricing->best()) {
                    unset($plans[$key]);
                    continue;
                }
            }
        }
        $ca->assign("plans", $plans);
        $ca->assign("inPreview", $isAdminPreview);
        $ca->setTemplate("store/marketgoo/index");
        $ca->skipMainBodyContainer();
        return $ca;
    }
}

?>