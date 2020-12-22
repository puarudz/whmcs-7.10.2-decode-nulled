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

class PaypalController
{
    public function createOrder(\WHMCS\Http\Message\ServerRequest $request)
    {
        $requestBody = $this->decodeJsonBodyResponse((string) $request->getBody());
        check_token("WHMCS.default", $requestBody["token"]);
        $invoiceId = (int) $request->get("invoiceid");
        $forceOneTime = $requestBody["forceonetime"];
        try {
            if (0 < $invoiceId) {
                $client = \WHMCS\User\Client::loggedIn()->first();
                $invoice = \WHMCS\Billing\Invoice::findOrFail($invoiceId);
                if ($invoice->userid != $client->id) {
                    throw new \WHMCS\Exception("Invalid request.");
                }
                $cart = $invoice->cart();
                $intent = "CAPTURE";
            } else {
                $cart = \WHMCS\Cart\Cart::fromSession();
                $intent = "AUTHORIZE";
                $invoice = null;
            }
            $gatewayInterface = \WHMCS\Module\Gateway::factory("paypalcheckout");
            $settings = $gatewayInterface->loadSettings();
            if (paypalcheckout_isRecurringPossible($cart) && !$forceOneTime) {
                return new \WHMCS\Http\Message\JsonResponse(array("paypalPlanId" => $this->createPlanId($cart, $settings["clientId"])));
            }
            $paypalApi = new PaypalApi();
            $total = $cart->getTotal()->toNumeric();
            $currency = $cart->total->getCurrency()["code"];
            $orderId = $paypalApi->createOrder(\WHMCS\View\Formatter\Price::adjustDecimals($total, $currency), $currency, $intent, \WHMCS\Config\Setting::getValue("CompanyName"), $cart->client, $invoice);
            return new \WHMCS\Http\Message\JsonResponse(array("paypalOrderId" => $orderId));
        } catch (Exception\AuthError $e) {
            return new \WHMCS\Http\Message\JsonResponse(array("error" => "PayPal API Authentication failed."));
        } catch (\Exception $e) {
            return new \WHMCS\Http\Message\JsonResponse(array("error" => "PayPal Create Order Error: " . $e->getMessage()));
        }
    }
    public function createPlanId($cart, $clientId)
    {
        $firstItem = $cart->getFirstRecurringItem();
        $paypalProduct = \WHMCS\Payment\PaymentGatewayProductMapping::gateway("paypal")->accountIdentifier(md5($clientId))->productIdentifier($firstItem->id)->first();
        if (is_null($paypalProduct)) {
            $paypalApi = new PaypalApi();
            $paypalProduct = new \WHMCS\Payment\PaymentGatewayProductMapping();
            $paypalProduct->gateway = "paypal";
            $paypalProduct->account_identifier = md5($clientId);
            $paypalProduct->product_identifier = $firstItem->id;
            $paypalProduct->remote_identifier = $paypalApi->createProduct($firstItem->name, $cart->getDescription());
            if (!$paypalProduct->remote_identifier) {
                throw new \WHMCS\Exception("Unable to create product. Please refer to the module log for further details.");
            }
            $paypalProduct->save();
        }
        $initialPeriod = $initialCycle = null;
        if ($firstItem->hasInitialPeriod()) {
            $initialPeriod = $firstItem->initialPeriod;
            $initialCycle = $firstItem->initialCycle;
        }
        $paypalApi = new PaypalApi();
        $total = $cart->getTotal()->toNumeric();
        $recurringTotal = $cart->getRecurringTotal();
        $currency = $cart->total->getCurrency()["code"];
        $planId = $paypalApi->createProductPlan($paypalProduct->remote_identifier, $firstItem->name, $cart->getDescription(), \WHMCS\View\Formatter\Price::adjustDecimals($total, $currency), \WHMCS\View\Formatter\Price::adjustDecimals($recurringTotal, $currency), $firstItem->billingCycle, $firstItem->billingPeriod, $currency, $initialCycle, $initialPeriod);
        return $planId;
    }
    public function validateOrder(\WHMCS\Http\Message\ServerRequest $request)
    {
        $requestBody = $this->decodeJsonBodyResponse((string) $request->getBody());
        check_token("WHMCS.default", $requestBody["token"]);
        $paypalOrderId = $requestBody["paypalorderid"];
        $paypalSubscriptionId = $requestBody["paypalsubid"];
        $paypalApi = new PaypalApi();
        $details = $paypalApi->getOrderDetails($paypalOrderId);
        $payer = $details->payer;
        $payerFirstName = $payer->name->given_name;
        $payerLastName = $payer->name->surname;
        $payerEmail = $payer->email_address;
        $shippingAddress = $details->purchase_units[0]->shipping->address;
        $state = (new \WHMCS\Utility\Country())->getStateNameFromCode($shippingAddress->country_code, $shippingAddress->admin_area_1);
        $redirectUrl = (new \WHMCS\OrderForm())->startExpressCheckout("paypalcheckout", array("orderId" => $paypalOrderId, "subscriptionId" => $paypalSubscriptionId, "paypalEmail" => $payerEmail), array("firstname" => $payerFirstName, "lastname" => $payerLastName, "email" => $payerEmail, "address1" => $shippingAddress->address_line_1, "city" => $shippingAddress->admin_area_2, "state" => $state, "postcode" => $shippingAddress->postal_code, "country" => $shippingAddress->country_code));
        return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "redirectUrl" => $redirectUrl));
    }
    public function verifyPayment(\WHMCS\Http\Message\ServerRequest $request)
    {
        $requestBody = $this->decodeJsonBodyResponse((string) $request->getBody());
        check_token("WHMCS.default", $requestBody["token"]);
        $invoiceId = $requestBody["invoiceid"];
        $paypalOrderId = $requestBody["paypalorderid"];
        $paypalSubscriptionId = $requestBody["paypalsubid"];
        $invoice = \WHMCS\Billing\Invoice::find($invoiceId);
        if (!$invoice || $invoice->userId != \WHMCS\User\Client::loggedIn()->first()->id) {
            throw new \WHMCS\Exception("Invalid request.");
        }
        $paypalApi = new PaypalApi();
        $response = $paypalApi->getOrderDetails($paypalOrderId);
        if ($response->status == "COMPLETED") {
            return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "reason" => "alreadyCompleted"));
        }
        if ($response->status == "APPROVED" || $response->status == "CREATED") {
            if ($paypalSubscriptionId) {
                $paypalApi->getSubscriptionDetails($paypalSubscriptionId);
                $invoice->saveSubscriptionId($paypalSubscriptionId);
                $invoice->setStatusPending()->save();
                return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "redirectUrl" => "viewinvoice.php?id=" . $invoice->id . "&paymentinititated=true"));
            }
            $captureResponse = $paypalApi->captureOrder($paypalOrderId);
            if ($captureResponse->status == "COMPLETED") {
                if (1 < count($captureResponse->purchase_units) || 1 < count($captureResponse->purchase_units[0]->payments->captures)) {
                    throw new Exception\PaymentIncomplete("Unexpected number of purchase units or captures: " . count($captureResponse->purchase_units));
                }
                $captureData = $captureResponse->purchase_units[0]->payments->captures[0];
                $currency = $captureData->amount->currency_code;
                if ($captureData->status == "COMPLETED") {
                    if ($currency != $invoice->getCurrency()["code"]) {
                        return new \WHMCS\Http\Message\JsonResponse(array("success" => false, "reason" => "currencyMismatch"));
                    }
                    $invoice->addPayment($captureData->amount->value, $captureData->id, $captureData->seller_receivable_breakdown->paypal_fee->value, "paypalcheckout");
                    return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "redirectUrl" => "viewinvoice.php?id=" . $invoice->id . "&paymentsuccess=true"));
                }
                if ($captureData->status == "PENDING") {
                    return new \WHMCS\Http\Message\JsonResponse(array("success" => true, "redirectUrl" => "viewinvoice.php?id=" . $invoice->id . "&paymentinititated=true"));
                }
                return new \WHMCS\Http\Message\JsonResponse(array("success" => false, "reason" => "captureStatusIncomplete"));
            }
            return new \WHMCS\Http\Message\JsonResponse(array("success" => false, "reason" => "statusIncomplete", "data" => $captureResponse));
        }
        throw new \WHMCS\Exception("Order not in capturable state.");
    }
    public function verifySubscriptionSetup(\WHMCS\Http\Message\ServerRequest $request)
    {
        $invoiceId = $request->get("invoice_id");
        $paypalSubscriptionId = $request->get("subscription_id");
        $invoice = \WHMCS\Billing\Invoice::find($invoiceId);
        if (!$invoice || $invoice->userId != \WHMCS\User\Client::loggedIn()->first()->id) {
            throw new \WHMCS\Exception("Invalid request.");
        }
        $response = (new PaypalApi())->getSubscriptionDetails($paypalSubscriptionId);
        if ($response->getFromResponse("status") !== "ACTIVE") {
            return new \Zend\Diactoros\Response\RedirectResponse($invoice->getViewInvoiceUrl(array("paymentfailed" => true)));
        }
        $invoice->saveSubscriptionId($paypalSubscriptionId);
        if ($invoice->status === \WHMCS\Billing\Invoice::STATUS_UNPAID) {
            $invoice->setStatusPending()->save();
        }
        return new \Zend\Diactoros\Response\RedirectResponse($invoice->getViewInvoiceUrl(array("paymentsuccess" => true)));
    }
    protected function decodeJsonBodyResponse($response)
    {
        $requestBody = json_decode((string) $response, true);
        if (!is_array($requestBody) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \WHMCS\Exception("Invalid request");
        }
        return $requestBody;
    }
}

?>