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

class SitelockVPNController
{
    public function index(\WHMCS\Http\Message\ServerRequest $request)
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        if (!$isAdminPreview) {
            $service = Service::where("name", "sitelockvpn")->first();
            if (is_null($service) || !$service->status) {
                return new \Zend\Diactoros\Response\RedirectResponse("index.php");
            }
        }
        $ca = new Output\ClientArea();
        $ca->setPageTitle(\Lang::trans("store.sitelockvpn.title"));
        $ca->addToBreadCrumb("index.php", \Lang::trans("globalsystemname"));
        $ca->addToBreadCrumb(routePath("store"), \Lang::trans("navStore"));
        $ca->addToBreadCrumb(routePath("store-sitelockvpn-index"), \Lang::trans("store.sitelockvpn.title"));
        $ca->initPage();
        $sessionCurrency = \WHMCS\Session::get("currency");
        $currency = getCurrency($ca->getUserId(), $sessionCurrency);
        $ca->assign("activeCurrency", $currency);
        $sitelockPromoHelper = MarketConnect::factoryPromotionalHelper("sitelockvpn");
        $plans = \WHMCS\Product\Product::sitelockVPN()->visible()->get();
        $emulated = false;
        if ($isAdminPreview && !$plans) {
            $feed = new ServicesFeed();
            $plans = $feed->getEmulationOfConfiguredProducts("sitelockvpn");
            $pricingFeed = $feed->getTerms("sitelockvpn_standard");
            $emulated = true;
        }
        $highestMonthlyPrice = 0;
        $pricing = array();
        foreach ($plans as $plan) {
            if ($emulated) {
                foreach ($pricingFeed as $feedItem) {
                    if ($feedItem["term"] == 12) {
                        $term = "1 Year";
                    } else {
                        if ($feedItem["term"] == 1) {
                            $term = $feedItem["term"] . " Month";
                        } else {
                            $term = $feedItem["term"] . " Months";
                        }
                    }
                    $pricing[0][] = array("term" => $term, "price" => "-");
                }
            } else {
                $highestMonthlyPrice = $plan->pricing($currency)->getHighestMonthly();
                $pricing[$plan->id] = $plan->pricing($currency)->allAvailableCycles();
            }
            $plan->planFeatures = $sitelockPromoHelper->getPlanFeatures($plan->productKey);
        }
        $ca->assign("plans", $plans);
        $ca->assign("pricings", $pricing);
        $ca->assign("highestMonthlyPrice", $highestMonthlyPrice);
        $ca->assign("inPreview", $isAdminPreview);
        $ca->setTemplate("store/sitelockvpn/index");
        $ca->skipMainBodyContainer();
        return $ca;
    }
}

?>