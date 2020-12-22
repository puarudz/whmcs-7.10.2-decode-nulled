<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\Paypalcheckout;

class PaypalOnboarding
{
    protected $sandbox = false;
    protected $featuredPage = false;
    protected $sellerNonce = "";
    const LIVE_URL = "https://www.paypal.com/";
    const SANDBOX_URL = "https://www.sandbox.paypal.com/";
    const LIVE_API_URL = "https://api.paypal.com/";
    const SANDBOX_API_URL = "https://api.sandbox.paypal.com/";
    const LIVE_PARTNER_ID = "HA8JRU89P2JUL";
    const SANDBOX_PARTNER_ID = "2XXGCC6WVS9A4";
    public function __construct()
    {
        $this->partnerClientId = substr(sha1(\App::getLicense()->getLicenseKey()), 0, 10);
        $this->sellerNonce = (new \WHMCS\Utility\Random())->string(20, 20, 10, 0);
    }
    public function enableSandbox()
    {
        $this->sandbox = true;
        return $this;
    }
    public function isFeaturedPage()
    {
        $this->featuredPage = true;
        return $this;
    }
    public function getOnboardCompleteJsFunctionName()
    {
        if ($this->featuredPage) {
            return "paypalCheckoutOnboardingCompleteFeatured";
        }
        return "paypalCheckoutOnboardingComplete";
    }
    protected function getPartnerId()
    {
        if ($this->sandbox) {
            return self::SANDBOX_PARTNER_ID;
        }
        return self::LIVE_PARTNER_ID;
    }
    protected function getUrl()
    {
        if ($this->sandbox) {
            return self::SANDBOX_URL;
        }
        return self::LIVE_URL;
    }
    public function getLinkUri()
    {
        $linkUrl = $this->getUrl() . "bizsignup/partner/entry";
        $params = array("partnerId" => $this->getPartnerId(), "product" => "EXPRESS_CHECKOUT", "integrationType" => "FO", "features" => "PAYMENT,REFUND", "partnerClientId" => $this->partnerClientId, "productIntentId" => "addipmt", "displayMode" => "minibrowser", "sellerNonce" => $this->sellerNonce);
        return $linkUrl . "?" . build_query_string($params);
    }
    protected function getApiUrl()
    {
        if ($this->sandbox) {
            return self::SANDBOX_API_URL;
        }
        return self::LIVE_API_URL;
    }
    protected function getSaveUrl()
    {
        return routePath("admin-setup-payments-gateways-onboarding-return");
    }
    public function getJs()
    {
        $csrfToken = generate_token("plain");
        $jsFunctionName = $this->getOnboardCompleteJsFunctionName();
        return "<script>\n    var paypalActionBtns = '#btnPayPalOnboardViaModule,#btnPayPalOffboardViaModule,#Payment-Gateway-Config-paypalcheckout input[type=\"submit\"]';\n    (function(d, s, id) {\n        var js, ref = d.getElementsByTagName(s)[0];\n        if (!d.getElementById(id)) {\n            js = d.createElement(s);\n            js.id = id;\n            js.async = true;\n            js.src = \"https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js\";\n            ref.parentNode.insertBefore(js, ref);\n        }\n    }(document, \"script\", \"paypal-js\"));\n    if (typeof " . $jsFunctionName . " === 'undefined') {\n        function " . $jsFunctionName . "(authCode, sharedId) {\n            var sellerNonce = '" . $this->sellerNonce . "';\n            jQuery.ajax({\n                url: '" . $this->getApiUrl() . "v1/oauth2/token',\n                type: 'post',\n                data: {\n                    grant_type: 'authorization_code',\n                    code: authCode,\n                    code_verifier: sellerNonce\n                },\n                headers: {\n                    \"Authorization\": 'Basic ' + btoa(sharedId + ':'),\n                    \"Content-Type\": 'text/plain',\n                    \"cache-control\": 'no-cache'\n                },\n                dataType: 'json',\n                success: function (data) {\n                    jQuery.ajax({\n                        url: '" . $this->getApiUrl() . "v1/customer/partners/" . $this->getPartnerId() . "/merchant-integrations/credentials/',\n                        type: 'get',\n                        headers: {\n                            \"Authorization\": 'Bearer ' + data.access_token,\n                            \"Content-Type\": 'application/json',\n                            \"cache-control\": 'no-cache'\n                        },\n                        dataType: 'json',\n                        success: function(data) {\n                            \$(paypalActionBtns).addClass('disabled');\n                            WHMCS.http.jqClient.jsonPost({\n                                url: \"" . $this->getSaveUrl() . "\",\n                                data: {\n                                    token: '" . $csrfToken . "',\n                                    gateway: 'paypalcheckout',\n                                    clientid: data.client_id,\n                                    clientsecret: data.client_secret,\n                                    json: 1\n                                },\n                                success: function(data) {\n                                    if (data.success) {\n                                        window.location = 'configgateways.php?updated=paypalcheckout&r=' + (new Date()).getTime() + '#m_paypalcheckout';\n                                    } else {\n                                        window.location = 'configgateways.php?obfailed=1&r=' + (new Date()).getTime();\n                                    }\n                                },\n                                error: function() {\n                                    jQuery.growl.error({ title: '', message: 'PayPal Onboarding Error 1. Please contact support.' });\n                                    \$(paypalActionBtns).removeClass('disabled');\n                                }\n                            });\n                        },\n                        error: function(jqXHR, textStatus, errorThrown) {\n                            jQuery.growl.error({ title: '', message: 'PayPal Onboarding Error 2: ' + errorThrown });\n                            \$(paypalActionBtns).removeClass('disabled');\n                        }\n                    });\n                },\n                error: function(jqXHR, textStatus, errorThrown) {\n                    jQuery.growl.error({ title: '', message: 'PayPal Onboarding Error 3: ' + errorThrown });\n                    \$(paypalActionBtns).removeClass('disabled');\n                }\n            });\n        }\n    }\n</script>";
    }
    public function getOffboardJs()
    {
        return "<script>\nfunction unlinkPaypalCheckout() {\n    var table = jQuery('#Payment-Gateway-Config-paypalcheckout'),\n        clientId = table.find('input[name=\"field[clientId]\"]'),\n        clientSecret = table.find('input[name=\"field[clientSecret]\"]'),\n        submit = table.closest('form');\n    clientId.val('');\n    clientSecret.val('');\n    submit.submit();\n}\n</script>";
    }
}

?>