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

class PaypalJsClient
{
    protected $params = array();
    protected $elements = array();
    protected $styleLabel = "checkout";
    protected $debug = false;
    const PARTNER_ID = "WHMCS_ST";
    const INTEGRATION_DATE = "2019-11-01";
    public function addParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }
    public function getParams()
    {
        $extras = array("integration-date" => self::INTEGRATION_DATE);
        if ($this->debug) {
            $extras["debug"] = "true";
        }
        return array_merge($this->params, $extras);
    }
    public function setStyleLabel($styleLabel)
    {
        $this->styleLabel = $styleLabel;
        return $this;
    }
    public function addCreateOrder($routeCreateOrder, $token, $forceOneTime = false)
    {
        $this->elements[] = "createOrder: function() {\n    return fetch('" . $routeCreateOrder . "', {\n        method: 'post',\n        headers: {\n          'content-type': 'application/json'\n        },\n        body: JSON.stringify({\n            token: '" . $token . "',\n            forceonetime: '" . $forceOneTime . "'\n        })\n    }).then(function(res) {\n        return res.json();\n    }).then(function(data) {\n        return data.paypalOrderId;\n    });\n}";
        return $this;
    }
    public function addCreateSubscription($routeCreateOrder, $token, $companyName, $firstName, $lastName, $email)
    {
        $this->addParam("vault", "true");
        $companyName = addslashes($companyName);
        $firstName = addslashes($firstName);
        $lastName = addslashes($lastName);
        $email = addslashes($email);
        $this->elements[] = "createSubscription: function (data, actions) {\n    return fetch('" . $routeCreateOrder . "', {\n        method: 'post',\n        headers: {\n            'content-type': 'application/json'\n        },\n        body: JSON.stringify({\n            token: '" . $token . "'\n        })\n    }).then(function(res) {\n        return res.json();\n    }).then(function (data) {\n        return actions.subscription.create({\n            'plan_id': data.paypalPlanId,\n            'subscriber': {\n                'name': {\n                  'given_name': '" . $firstName . "',\n                  'surname': '" . $lastName . "'\n                },\n                'email_address': '" . $email . "'\n              },\n              'auto_renewal': true,\n              'application_context': {\n                'brand_name': '" . $companyName . "',\n                'user_action': 'SUBSCRIBE_NOW',\n                'payment_method': {\n                  'payer_selected': 'PAYPAL',\n                  'payee_preferred': 'IMMEDIATE_PAYMENT_REQUIRED'\n                }\n              }\n        });\n    });\n}";
        return $this;
    }
    public function addOnApprove($routeVerifyPayment, $token, $invoiceId, $waitMsg = "Processing payment. Please wait...")
    {
        $this->elements[] = "onApprove: function(data, actions) {\n    showOverlay('" . $waitMsg . "');\n    return fetch('" . $routeVerifyPayment . "', {\n        method: 'post',\n        headers: {\n            'content-type': 'application/json'\n        },\n        body: JSON.stringify({\n            token: '" . $token . "',\n            invoiceid: '" . $invoiceId . "',\n            paypalorderid: data.orderID,\n            paypalsubid: data.subscriptionID\n        })\n    }).then(function(res) {\n        return res.json();\n    }).then(function (data) {\n        if (data.success) {\n            if (data.redirectUrl) {\n                window.location = data.redirectUrl;\n            } else {\n                window.location.reload();\n            }\n        } else {\n            return actions.restart();\n        }\n    });\n}";
        return $this;
    }
    public function render($hidden = false)
    {
        $partnerId = self::PARTNER_ID;
        $jsParams = http_build_query($this->getParams());
        $styleLabel = $this->styleLabel;
        $elements = implode(",", $this->elements);
        $hidden = $hidden ? " style=\"display:none;\"" : "";
        return "<script src=\"https://www.paypal.com/sdk/js?" . $jsParams . "\" data-partner-attribution-id=\"" . $partnerId . "\"></script>\n<div id=\"paypal-button-container\"" . $hidden . "></div>\n<script>\nif (typeof paypal !== 'undefined') {\n    paypal.Buttons({\n      style: {\n        layout:  'vertical',\n        label: '" . $styleLabel . "'\n      },\n      " . $elements . ",\n      onError: function (err) {\n        jQuery('body').after('<div id=\"paypalErrorContainer\" style=\"position:fixed;top:0;left:0;width:100%;height:100%;text-align:center;background-color:#222;color:#fff;padding-top:100px;font-size:16px;z-index:10000;\">An unknown error occurred. Please try again.<br><br><button type=\"button\" class=\"btn btn-default\" onclick=\"jQuery(\\'#paypalErrorContainer\\').remove();\">Continue</button></div>');\n      }\n    }).render('#paypal-button-container');\n} else {\n    jQuery(document).ready(function() {\n        jQuery('#paypal-button-container').parent('.express-checkout-buttons').find('.separator').hide();\n    });\n}\n</script>";
    }
}

?>