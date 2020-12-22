<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
function acceptjs_MetaData()
{
    return array("RemoteInputFields" => false);
}
function acceptjs_config()
{
    return array("FriendlyName" => array("Type" => "System", "Value" => "Authorize.net Accept.js"), "apiLoginId" => array("FriendlyName" => "API Login ID", "Type" => "text", "Size" => "20", "Description" => "This can be found by navigating to Account > Security Settings > " . "API Credentials & Keys within your Authorize.net account."), "transactionKey" => array("FriendlyName" => "Transaction Key", "Type" => "text", "Size" => "20", "Description" => "This can be found by navigating to Account > Security Settings > " . "API Credentials & Keys within your Authorize.net account."), "publicKey" => array("FriendlyName" => "Public Client Key", "Type" => "text", "Size" => "40", "Description" => "This can be found by navigating to Account > Security Settings > " . "Manage Public Client Key within your Authorize.net account."), "testMode" => array("FriendlyName" => "Test Mode", "Type" => "yesno"), "noAccount" => array("Type" => "info", "Description" => "<div class=\"alert alert-info\" style=\"margin-bottom: 0;\">" . "Don't have an account? <a href=\"https://www.whmcs.com/start-accepting-credit-" . "cards\" class=\"alert-link autoLinked\">Apply for an account for free</a></div>"));
}
function acceptjs_config_validate(array $params = array())
{
    $apiUrl = net\authorize\api\constants\ANetEnvironment::PRODUCTION;
    if ($params["testMode"]) {
        $apiUrl = net\authorize\api\constants\ANetEnvironment::SANDBOX;
    }
    $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
    $merchantAuthentication->setName($params["apiLoginId"]);
    $merchantAuthentication->setTransactionKey($params["transactionKey"]);
    $test = new net\authorize\api\contract\v1\AuthenticateTestRequest();
    $test->setMerchantAuthentication($merchantAuthentication);
    $controller = new WHMCS\Module\Gateway\AcceptJs\AcceptJsAuthenticateTestController($test);
    if ($params["testMode"]) {
        $controller->httpClient->setVerifyHost(0);
        $controller->httpClient->setVerifyPeer(false);
    }
    $response = $controller->executeWithApiResponse($apiUrl);
    if ($response->getMessages()->getResultCode() != "Ok") {
        $errorMessages = $response->getMessages()->getMessage();
        if (!$errorMessages) {
            $errorMessage = "An unknown error occurred with the configuration check.";
        } else {
            $errorMessage = $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText();
        }
        throw new WHMCS\Exception\Module\InvalidConfiguration($errorMessage);
    }
}
function acceptjs_capture(array $params = array())
{
    $gatewayId = json_decode(WHMCS\Input\Sanitize::decode($params["gatewayid"]), true);
    $apiUrl = net\authorize\api\constants\ANetEnvironment::PRODUCTION;
    if ($params["testMode"]) {
        $apiUrl = net\authorize\api\constants\ANetEnvironment::SANDBOX;
    }
    if ((!is_array($gatewayId) || json_last_error() !== JSON_ERROR_NONE) && !$params["cardnum"] && $params["gatewayid"]) {
        $gatewayId = explode(",", $params["gatewayid"]);
        if (count($gatewayId) == 3) {
            list($customerProfileId, $customerPaymentProfileId) = $gatewayId;
            $gatewayId = array();
            $gatewayId["customer"] = $customerProfileId;
            $gatewayId["payment"] = $customerPaymentProfileId;
            invoiceSetPayMethodRemoteToken($params["invoiceid"], json_encode($gatewayId));
        } else {
            $gatewayId = "";
        }
    } else {
        if ((!is_array($gatewayId) || json_last_error() !== JSON_ERROR_NONE) && $params["cardnum"]) {
            try {
                $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
                $merchantAuthentication->setName($params["apiLoginId"]);
                $merchantAuthentication->setTransactionKey($params["transactionKey"]);
                $refId = "ref" . time();
                $creditCard = new net\authorize\api\contract\v1\CreditCardType();
                $creditCard->setCardNumber($params["cardnum"]);
                $cardExpiry = "20" . substr($params["cardexp"], 2, 2) . "-" . substr($params["cardexp"], 0, 2);
                $creditCard->setExpirationDate($cardExpiry);
                if ($params["cccvv"]) {
                    $creditCard->setCardCode($params["cccvv"]);
                }
                $paymentCreditCard = new net\authorize\api\contract\v1\PaymentType();
                $paymentCreditCard->setCreditCard($creditCard);
                $billTo = new net\authorize\api\contract\v1\CustomerAddressType();
                $billTo->setFirstName($params["clientdetails"]["firstname"]);
                $billTo->setLastName($params["clientdetails"]["lastname"]);
                $billTo->setCompany($params["clientdetails"]["companyname"]);
                $billTo->setAddress($params["clientdetails"]["address1"]);
                $billTo->setCity($params["clientdetails"]["city"]);
                $billTo->setState($params["clientdetails"]["state"]);
                $billTo->setZip($params["clientdetails"]["postcode"]);
                $billTo->setCountry($params["clientdetails"]["countryName"]);
                $billTo->setPhoneNumber($params["clientdetails"]["phonenumber"]);
                $paymentProfile = new net\authorize\api\contract\v1\CustomerPaymentProfileType();
                $paymentProfile->setCustomerType("individual");
                $paymentProfile->setBillTo($billTo);
                $paymentProfile->setPayment($paymentCreditCard);
                $paymentProfile->setDefaultpaymentProfile(true);
                $paymentProfiles[] = $paymentProfile;
                $customerProfile = new net\authorize\api\contract\v1\CustomerProfileType();
                $customerProfile->setDescription($params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"]);
                $customerProfile->setMerchantCustomerId("M_" . time());
                $customerProfile->setEmail($params["clientdetails"]["email"]);
                $customerProfile->setpaymentProfiles($paymentProfiles);
                $request = new net\authorize\api\contract\v1\CreateCustomerProfileRequest();
                $request->setMerchantAuthentication($merchantAuthentication);
                $request->setRefId($refId);
                $request->setProfile($customerProfile);
                $controller = new net\authorize\api\controller\CreateCustomerProfileController($request);
                $response = $controller->executeWithApiResponse($apiUrl);
                if ($response != NULL && $response->getMessages()->getResultCode() == "Ok") {
                    $gatewayId = array();
                    $gatewayId["customer"] = $response->getCustomerProfileId();
                    $paymentProfiles = $response->getCustomerPaymentProfileIdList();
                    $gatewayId["payment"] = $paymentProfiles[0];
                    invoiceSetPayMethodRemoteToken($params["invoiceid"], json_encode($gatewayId));
                } else {
                    $errorMessages = $response->getMessages()->getMessage();
                    $errorMessage = "Response : " . $errorMessages[0]->getCode() . " " . $errorMessages[0]->getText();
                    return array("status" => "error", "rawdata" => "Invalid Response: " . $errorMessage);
                }
            } catch (Exception $e) {
                return array("status" => "error", "rawdata" => "Invalid Response: " . $e->getMessage());
            }
        }
    }
    if (!is_array($gatewayId) || !($gatewayId["customer"] && $gatewayId["payment"])) {
        return array("status" => "error", "rawdata" => "No Data Stored for Authorize.net Accept JS");
    }
    try {
        $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
        $merchantAuthentication->setName($params["apiLoginId"]);
        $merchantAuthentication->setTransactionKey($params["transactionKey"]);
        $refId = "ref" . time();
        $profileToCharge = new net\authorize\api\contract\v1\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($gatewayId["customer"]);
        $paymentProfile = new net\authorize\api\contract\v1\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($gatewayId["payment"]);
        $profileToCharge->setPaymentProfile($paymentProfile);
        $transactionRequestType = new net\authorize\api\contract\v1\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($params["amount"]);
        $transactionRequestType->setProfile($profileToCharge);
        $userFields = array();
        $userField = new net\authorize\api\contract\v1\UserFieldType();
        $userField->setName("invoice_id");
        $userField->setValue($params["invoiceid"]);
        $userFields[] = $userField;
        $transactionRequestType->setUserFields($userFields);
        if (!$params["testMode"]) {
            $transactionRequestType->setSolution((new net\authorize\api\contract\v1\SolutionType())->setId("AAA172608"));
        } else {
            $transactionRequestType->setSolution((new net\authorize\api\contract\v1\SolutionType())->setId("AAA100302"));
        }
        $request = new net\authorize\api\contract\v1\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new net\authorize\api\controller\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($apiUrl);
        if ($response != NULL) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();
                if ($tresponse != NULL && $tresponse->getMessages() != NULL) {
                    $cardLastFour = substr($tresponse->getAccountNumber(), -4);
                    $cardType = $tresponse->getAccountType();
                    if (strtolower($cardType) == "americanexpress") {
                        $cardType = "American Express";
                    }
                    invoiceSaveRemoteCard($params["invoiceid"], $cardLastFour, $cardType, NULL, json_encode($gatewayId));
                    $rawData = (array) $tresponse;
                    foreach ($rawData as $key => $value) {
                        unset($rawData[$key]);
                        $key = str_replace("net\\authorize\\api\\contract\\v1\\TransactionResponseType", "", $key);
                        if (is_object($value)) {
                            $value = (array) $value;
                        }
                        $rawData[$key] = $value;
                    }
                    return array("status" => "success", "transid" => $tresponse->getTransId(), "amount" => $params["amount"], "rawdata" => (array) $rawData);
                } else {
                    return array("status" => "declined", "rawdata" => array("Error Code" => $tresponse->getErrors()[0]->getErrorCode(), "Error Message" => $tresponse->getErrors()[0]->getErrorText()));
                }
            } else {
                $tresponse = $response->getTransactionResponse();
                if ($tresponse != NULL && $tresponse->getErrors() != NULL) {
                    $errorCode = $tresponse->getErrors()[0]->getErrorCode();
                    $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                } else {
                    $errorCode = $response->getMessages()->getMessage()[0]->getCode();
                    $errorMessage = $response->getMessages()->getMessage()[0]->getText();
                }
                return array("status" => "error", "rawdata" => array("Error Code" => $errorCode, "Error Message" => $errorMessage));
            }
        } else {
            return array("status" => "error", "rawdata" => array("Error" => "No response returned \n"));
        }
    } catch (Exception $e) {
        return array("status" => "error", "rawdata" => array("File Error" => $e->getFile(), "File Number" => $e->getLine(), "Error Message" => $e->getMessage()));
    }
}
function acceptjs_refund(array $params = array())
{
    $gatewayId = json_decode(WHMCS\Input\Sanitize::decode($params["gatewayid"]), true);
    $apiUrl = net\authorize\api\constants\ANetEnvironment::PRODUCTION;
    if ($params["testMode"]) {
        $apiUrl = net\authorize\api\constants\ANetEnvironment::SANDBOX;
    }
    if ((!is_array($gatewayId) || json_last_error() !== JSON_ERROR_NONE) && !$params["cardnum"] && $params["gatewayid"]) {
        $gatewayId = explode(",", $params["gatewayid"]);
        if (count($gatewayId) == 3) {
            list($customerProfileId, $customerPaymentProfileId) = $gatewayId;
            $gatewayId = array();
            $gatewayId["customer"] = $customerProfileId;
            $gatewayId["payment"] = $customerPaymentProfileId;
            invoiceSetPayMethodRemoteToken($params["invoiceid"], json_encode($gatewayId));
        } else {
            $gatewayId = "";
        }
    }
    if (!is_array($gatewayId) || !($gatewayId["customer"] && $gatewayId["payment"])) {
        return array("status" => "error", "rawdata" => "No Data Stored for Authorize.net Accept JS");
    }
    try {
        $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
        $merchantAuthentication->setName($params["apiLoginId"]);
        $merchantAuthentication->setTransactionKey($params["transactionKey"]);
        $refId = "ref" . time();
        $creditCard = new net\authorize\api\contract\v1\CreditCardType();
        $creditCard->setCardNumber($params["clientdetails"]["cclastfour"]);
        $creditCard->setExpirationDate("XXXX");
        $paymentOne = new net\authorize\api\contract\v1\PaymentType();
        $paymentOne->setCreditCard($creditCard);
        $transactionRequest = new net\authorize\api\contract\v1\TransactionRequestType();
        $transactionRequest->setTransactionType("refundTransaction");
        $transactionRequest->setAmount($params["amount"]);
        $transactionRequest->setPayment($paymentOne);
        $transactionRequest->setRefTransId($params["transid"]);
        $userFields = array();
        $userField = new net\authorize\api\contract\v1\UserFieldType();
        $userField->setName("invoice_id");
        $userField->setValue($params["invoiceid"]);
        $userFields[] = $userField;
        $transactionRequest->setUserFields($userFields);
        if (!$params["testMode"]) {
            $transactionRequest->setSolution((new net\authorize\api\contract\v1\SolutionType())->setId("AAA172608"));
        } else {
            $transactionRequest->setSolution((new net\authorize\api\contract\v1\SolutionType())->setId("AAA100302"));
        }
        $request = new net\authorize\api\contract\v1\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequest);
        $controller = new net\authorize\api\controller\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($apiUrl);
        if ($response != NULL) {
            $tresponse = $response->getTransactionResponse();
            if ($response->getMessages()->getResultCode() == "Ok") {
                if ($tresponse != NULL && $tresponse->getMessages() != NULL) {
                    $rawData = (array) $tresponse;
                    foreach ($rawData as $key => $value) {
                        unset($rawData[$key]);
                        $key = str_replace("net\\authorize\\api\\contract\\v1\\TransactionResponseType", "", $key);
                        if (is_object($value)) {
                            $value = (array) $value;
                        }
                        $rawData[$key] = $value;
                    }
                    return array("status" => "success", "transid" => $tresponse->getTransId(), "amount" => $params["amount"], "rawdata" => (array) $rawData);
                } else {
                    if ($tresponse->getErrors() != NULL) {
                        return array("status" => "declined", "rawdata" => array("Error Code" => $tresponse->getErrors()[0]->getErrorCode(), "Error Message" => $tresponse->getErrors()[0]->getErrorText()));
                    }
                }
            } else {
                if ($tresponse != NULL && $tresponse->getErrors() != NULL) {
                    $errorCode = $tresponse->getErrors()[0]->getErrorCode();
                    $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                } else {
                    $errorCode = $response->getMessages()->getMessage()[0]->getCode();
                    $errorMessage = $response->getMessages()->getMessage()[0]->getText();
                }
                return array("status" => "error", "rawdata" => array("Error Code" => $errorCode, "Error Message" => $errorMessage));
            }
        }
        return array("status" => "error", "rawdata" => array("Error" => "No response returned \n"));
    } catch (Exception $e) {
        return array("status" => "error", "rawdata" => array("File Error" => $e->getFile(), "File Number" => $e->getLine(), "Error Message" => $e->getMessage()));
    }
}
function acceptjs_storeremote(array $params = array())
{
    if (WHMCS\Session::get("cartccdetail")) {
        return array();
    }
    $apiUrl = net\authorize\api\constants\ANetEnvironment::PRODUCTION;
    if ($params["testMode"]) {
        $apiUrl = net\authorize\api\constants\ANetEnvironment::SANDBOX;
    }
    $dataDescriptor = WHMCS\Session::getAndDelete("dataDescriptor");
    if (!$dataDescriptor && App::isInRequest("dataDescriptor")) {
        $dataDescriptor = (string) App::getFromRequest("dataDescriptor");
    }
    $dataValue = WHMCS\Session::getAndDelete("dataValue");
    if (!$dataValue && App::isInRequest("dataValue")) {
        $dataValue = (string) App::getFromRequest("dataValue");
    }
    $payMethod = $params["payMethod"];
    $payment = $payMethod->payment;
    $client = $payMethod->client;
    if ($params["action"] === "create") {
        foreach ($client->payMethods as $tempPayMethod) {
            if ($tempPayMethod->gateway_name === "acceptjs") {
                $tempPayment = $tempPayMethod->payment;
                $gatewayId = json_decode($tempPayment->getRemoteToken(), true);
                if ($gatewayId && is_array($gatewayId)) {
                    break;
                }
                unset($gatewayId["payment"]);
            }
        }
    } else {
        $gatewayId = json_decode($payment->getRemoteToken(), true);
    }
    $billingContact = $payMethod->contact;
    if (!$gatewayId || !is_array($gatewayId) || json_last_error() !== JSON_ERROR_NONE) {
        if ($dataValue && $dataDescriptor) {
            try {
                $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
                $merchantAuthentication->setName($params["apiLoginId"]);
                $merchantAuthentication->setTransactionKey($params["transactionKey"]);
                $refId = "ref" . time();
                $opaqueData = new net\authorize\api\contract\v1\OpaqueDataType();
                $opaqueData->setDataDescriptor($dataDescriptor);
                $opaqueData->setDataValue($dataValue);
                $paymentOne = new net\authorize\api\contract\v1\PaymentType();
                $paymentOne->setOpaqueData($opaqueData);
                $customerAddress = new net\authorize\api\contract\v1\CustomerAddressType();
                $customerAddress->setFirstName($billingContact->firstName);
                $customerAddress->setLastName($billingContact->lastName);
                $customerAddress->setCompany($billingContact->companyName);
                $customerAddress->setAddress($billingContact->address1);
                $customerAddress->setCity($billingContact->city);
                $customerAddress->setState($billingContact->state);
                $customerAddress->setZip($billingContact->postcode);
                $customerAddress->setCountry($billingContact->countryName);
                $customerData = new net\authorize\api\contract\v1\CustomerPaymentProfileType();
                $customerData->setCustomerType("individual");
                $customerData->setBillTo($customerAddress);
                $customerData->setPayment($paymentOne);
                $customerData->setDefaultPaymentProfile(true);
                $paymentProfiles[] = $customerData;
                $customerProfile = new net\authorize\api\contract\v1\CustomerProfileType();
                $customerProfile->setDescription($client->fullName);
                $customerProfile->setMerchantCustomerId("M_" . time());
                $customerProfile->setEmail($client->email);
                $customerProfile->setpaymentProfiles($paymentProfiles);
                $paymentProfileRequest = new net\authorize\api\contract\v1\CreateCustomerProfileRequest();
                $paymentProfileRequest->setMerchantAuthentication($merchantAuthentication);
                $paymentProfileRequest->setRefId($refId);
                $paymentProfileRequest->setProfile($customerProfile);
                $controller = new net\authorize\api\controller\CreateCustomerProfileController($paymentProfileRequest);
                $response = $controller->executeWithApiResponse($apiUrl);
                if ($response != NULL && $response->getMessages()->getResultCode() == "Ok") {
                    $customerProfileId = $response->getCustomerProfileId();
                    $paymentProfiles = $response->getCustomerPaymentProfileIdList();
                    $customerPaymentProfileId = $paymentProfiles[0];
                    $gatewayId = array("customer" => $customerProfileId, "payment" => $customerPaymentProfileId);
                    $rawData = (array) $response;
                    $customerProfileId = $gatewayId["customer"];
                    $customerPaymentProfileId = $gatewayId["payment"];
                    $request = new net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest();
                    $request->setMerchantAuthentication($merchantAuthentication);
                    $request->setRefId($refId);
                    $request->setCustomerProfileId($customerProfileId);
                    $request->setCustomerPaymentProfileId($customerPaymentProfileId);
                    $request->setUnmaskExpirationDate(true);
                    $controller = new net\authorize\api\controller\GetCustomerPaymentProfileController($request);
                    $response = $controller->executeWithApiResponse($apiUrl);
                    if ($response != NULL) {
                        if ($response->getMessages()->getResultCode() == "Ok") {
                            $cardType = $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardType();
                            $cardLastFour = $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardNumber();
                            if (4 < strlen($cardLastFour)) {
                                $cardLastFour = substr($cardLastFour, -4, 4);
                            }
                            $cardExpiry = $response->getPaymentProfile()->getPayment()->getCreditCard()->getExpirationDate();
                            $cardExpiry = explode("-", $cardExpiry);
                            if (count($cardExpiry) == 2) {
                                $cardExpiry = $cardExpiry[1] . substr($cardExpiry[0], 2, 2);
                            } else {
                                $cardExpiry = "";
                            }
                            if (strtolower($cardType) == "americanexpress") {
                                $cardType = "American Express";
                            }
                            foreach ($rawData as $key => $value) {
                                unset($rawData[$key]);
                                $key = str_replace("net\\authorize\\api\\contract\\v1\\CreateCustomerProfileResponse", "", $key);
                                if (is_object($value)) {
                                    $value = (array) $value;
                                }
                                $rawData[$key] = $value;
                            }
                            $rawData2 = (array) $response;
                            foreach ($rawData2 as $key => $value) {
                                unset($rawData2[$key]);
                                $key = str_replace("net\\authorize\\api\\contract\\v1\\ANetApiResponseType", "", $key);
                                if (is_object($value)) {
                                    $value = (array) $value;
                                }
                                $rawData2[$key] = $value;
                            }
                            $rawData["setProfile"] = $rawData2;
                            if (!$cardLastFour) {
                                $cardLastFour = "XXXX";
                            }
                            return array("nodelete" => true, "cardlastfour" => $cardLastFour, "cardtype" => $cardType, "cardexpiry" => $cardExpiry, "gatewayid" => json_encode($gatewayId), "status" => "success", "rawdata" => $rawData);
                        } else {
                            $errorMessages = $response->getMessages()->getMessage();
                            $error = "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";
                            throw new WHMCS\Exception($error);
                        }
                    } else {
                        throw new WHMCS\Exception("Null Response");
                    }
                } else {
                    $errorMessages = $response->getMessages()->getMessage();
                    throw new WHMCS\Exception("Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText());
                }
            } catch (Exception $e) {
                return array("status" => "error", "rawdata" => array("dataDescriptor" => $dataDescriptor, "dataValue" => $dataValue, "error" => $e->getMessage()));
            }
        }
    } else {
        if ($gatewayId && is_array($gatewayId) && $dataValue && $dataDescriptor) {
            try {
                $customerProfileId = $gatewayId["customer"];
                $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
                $merchantAuthentication->setName($params["apiLoginId"]);
                $merchantAuthentication->setTransactionKey($params["transactionKey"]);
                $refId = "ref" . time();
                $opaqueData = new net\authorize\api\contract\v1\OpaqueDataType();
                $opaqueData->setDataDescriptor($dataDescriptor);
                $opaqueData->setDataValue($dataValue);
                $customerAddress = new net\authorize\api\contract\v1\CustomerAddressType();
                $customerAddress->setFirstName($billingContact->firstName);
                $customerAddress->setLastName($billingContact->lastName);
                $customerAddress->setCompany($billingContact->companyName);
                $customerAddress->setAddress($billingContact->address1);
                $customerAddress->setCity($billingContact->city);
                $customerAddress->setState($billingContact->state);
                $customerAddress->setZip($billingContact->postcode);
                $customerAddress->setCountry($billingContact->countryName);
                $paymentOne = new net\authorize\api\contract\v1\PaymentType();
                $paymentOne->setOpaqueData($opaqueData);
                $paymentProfile = new net\authorize\api\contract\v1\CustomerPaymentProfileType();
                $paymentProfile->setCustomerType("individual");
                $paymentProfile->setBillTo($customerAddress);
                $paymentProfile->setPayment($paymentOne);
                $paymentProfile->setDefaultPaymentProfile($payMethod->isDefaultPayMethod());
                $paymentProfiles[] = $paymentProfile;
                $paymentProfileRequest = new net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest();
                $paymentProfileRequest->setMerchantAuthentication($merchantAuthentication);
                $paymentProfileRequest->setCustomerProfileId($customerProfileId);
                $paymentProfileRequest->setPaymentProfile($paymentProfile);
                $paymentProfileRequest->setValidationMode($params["testMode"] ? "testMode" : "liveMode");
                $controller = new net\authorize\api\controller\CreateCustomerPaymentProfileController($paymentProfileRequest);
                $response = $controller->executeWithApiResponse($apiUrl);
                if ($response != NULL && $response->getMessages()->getResultCode() == "Ok") {
                    $customerPaymentProfileId = $response->getCustomerPaymentProfileId();
                    $gatewayId = array("customer" => $customerProfileId, "payment" => $customerPaymentProfileId);
                    $rawData = (array) $response;
                    foreach ($rawData as $key => $value) {
                        unset($rawData[$key]);
                        $key = str_replace("net\\authorize\\api\\contract\\v1\\ANetApiResponseType", "", $key);
                        if (is_object($value)) {
                            $value = (array) $value;
                        }
                        $rawData[$key] = $value;
                    }
                    $customerProfileId = $gatewayId["customer"];
                    $customerPaymentProfileId = $gatewayId["payment"];
                    $request = new net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest();
                    $request->setMerchantAuthentication($merchantAuthentication);
                    $request->setRefId($refId);
                    $request->setCustomerProfileId($customerProfileId);
                    $request->setCustomerPaymentProfileId($customerPaymentProfileId);
                    $request->setUnmaskExpirationDate(true);
                    $controller = new net\authorize\api\controller\GetCustomerPaymentProfileController($request);
                    $response = $controller->executeWithApiResponse($apiUrl);
                    $cardLastFour = "UNKN";
                    $cardType = "Visa";
                    $cardExpiry = "";
                    $rawData2 = (array) $response;
                    foreach ($rawData2 as $key => $value) {
                        unset($rawData2[$key]);
                        $key = str_replace("net\\authorize\\api\\contract\\v1\\ANetApiResponseType", "", $key);
                        if (is_object($value)) {
                            $value = (array) $value;
                        }
                        $rawData2[$key] = $value;
                    }
                    $rawData["paymentProfile"] = $rawData2;
                    if ($response != NULL && $response->getMessages()->getResultCode() == "Ok") {
                        $cardType = $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardType();
                        $cardLastFour = $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardNumber();
                        if (4 < strlen($cardLastFour)) {
                            $cardLastFour = substr($cardLastFour, -4, 4);
                        }
                        $cardExpiry = $response->getPaymentProfile()->getPayment()->getCreditCard()->getExpirationDate();
                        $cardExpiry = explode("-", $cardExpiry);
                        if (count($cardExpiry) == 2) {
                            $cardExpiry = $cardExpiry[1] . substr($cardExpiry[0], 2, 2);
                        } else {
                            $cardExpiry = "";
                        }
                        if (strtolower($cardType) == "americanexpress") {
                            $cardType = "American Express";
                        }
                    }
                    if (!$cardLastFour) {
                        $cardLastFour = "XXXX";
                    }
                    return array("nodelete" => true, "cardlastfour" => $cardLastFour, "cardtype" => $cardType, "cardexpiry" => $cardExpiry, "gatewayid" => json_encode($gatewayId), "status" => "success", "rawdata" => $rawData);
                } else {
                    $errorMessages = $response->getMessages()->getMessage();
                    throw new WHMCS\Exception("Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText());
                }
            } catch (Exception $e) {
                return array("status" => "error", "rawdata" => array("dataDescriptor" => $dataDescriptor, "dataValue" => $dataValue, "error" => $e->getMessage()));
            }
        } else {
            if ($gatewayId && is_array($gatewayId) && $params["action"] === "update") {
                try {
                    $customerProfileId = $gatewayId["customer"];
                    $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
                    $merchantAuthentication->setName($params["apiLoginId"]);
                    $merchantAuthentication->setTransactionKey($params["transactionKey"]);
                    $creditCard = new net\authorize\api\contract\v1\CreditCardType();
                    $creditCard->setCardNumber("XXXXXXXXXXXX" . $payment->getLastFour());
                    $creditCard->setExpirationDate(WHMCS\Carbon::createFromCcInput($params["cardexp"])->format("Y-m"));
                    $customerAddress = new net\authorize\api\contract\v1\CustomerAddressType();
                    $customerAddress->setFirstName($billingContact->firstName);
                    $customerAddress->setLastName($billingContact->lastName);
                    $customerAddress->setCompany($billingContact->companyName);
                    $customerAddress->setAddress($billingContact->address1);
                    $customerAddress->setCity($billingContact->city);
                    $customerAddress->setState($billingContact->state);
                    $customerAddress->setZip($billingContact->postcode);
                    $customerAddress->setCountry($billingContact->countryName);
                    $paymentOne = new net\authorize\api\contract\v1\PaymentType();
                    $paymentOne->setCreditCard($creditCard);
                    $paymentProfile = new net\authorize\api\contract\v1\CustomerPaymentProfileExType();
                    $paymentProfile->setCustomerType("individual");
                    $paymentProfile->setBillTo($customerAddress);
                    $paymentProfile->setPayment($paymentOne);
                    $paymentProfile->setDefaultPaymentProfile($payMethod->isDefaultPayMethod());
                    $paymentProfile->setCustomerPaymentProfileId($gatewayId["payment"]);
                    $paymentProfileRequest = new net\authorize\api\contract\v1\UpdateCustomerPaymentProfileRequest();
                    $paymentProfileRequest->setMerchantAuthentication($merchantAuthentication);
                    $paymentProfileRequest->setCustomerProfileId($customerProfileId);
                    $paymentProfileRequest->setPaymentProfile($paymentProfile);
                    $paymentProfileRequest->setValidationMode($params["testMode"] ? "testMode" : "liveMode");
                    $controller = new net\authorize\api\controller\UpdateCustomerPaymentProfileController($paymentProfileRequest);
                    $response = $controller->executeWithApiResponse($apiUrl);
                    if ($response != NULL && $response->getMessages()->getResultCode() == "Ok") {
                        $rawData = (array) $response;
                        foreach ($rawData as $key => $value) {
                            unset($rawData[$key]);
                            $key = str_replace("net\\authorize\\api\\contract\\v1\\ANetApiResponseType", "", $key);
                            if (is_object($value)) {
                                $value = (array) $value;
                            }
                            $rawData[$key] = $value;
                        }
                        return array("nodelete" => true, "cardlastfour" => $payment->getLastFour(), "cardtype" => $payment->getCardType(), "cardexpiry" => $params["cardexp"], "gatewayid" => json_encode($gatewayId), "status" => "success", "rawdata" => $rawData);
                    } else {
                        $errorMessages = $response->getMessages()->getMessage();
                        throw new WHMCS\Exception("Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText());
                    }
                } catch (Exception $e) {
                    return array("status" => "error", "rawdata" => array("error" => $e->getMessage()));
                }
            } else {
                if ($gatewayId && is_array($gatewayId) && $params["action"] === "delete") {
                    try {
                        $customerProfileId = $gatewayId["customer"];
                        $customerPaymentProfileId = $gatewayId["payment"];
                        $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
                        $merchantAuthentication->setName($params["apiLoginId"]);
                        $merchantAuthentication->setTransactionKey($params["transactionKey"]);
                        $refId = "ref" . time();
                        $request = new net\authorize\api\contract\v1\DeleteCustomerPaymentProfileRequest();
                        $request->setMerchantAuthentication($merchantAuthentication);
                        $request->setCustomerProfileId($customerProfileId);
                        $request->setCustomerPaymentProfileId($customerPaymentProfileId);
                        $controller = new net\authorize\api\controller\DeleteCustomerPaymentProfileController($request);
                        $response = $controller->executeWithApiResponse($apiUrl);
                        $rawData = (array) $response;
                        foreach ($rawData as $key => $value) {
                            unset($rawData[$key]);
                            $key = str_replace("net\\authorize\\api\\contract\\v1\\ANetApiResponseType", "", $key);
                            if (is_object($value)) {
                                $value = (array) $value;
                            }
                            $rawData[$key] = $value;
                        }
                        return array("status" => "success", "rawdata" => $rawData);
                    } catch (Exception $e) {
                        return array("status" => "error", "rawdata" => $e->getMessage());
                    }
                }
            }
        }
    }
    return array("status" => "error", "rawdata" => array("dataDescriptor" => $dataDescriptor, "dataValue" => $dataValue, "error" => "An unknown Error Occurred"));
}
function acceptjs_post_checkout(array $params = array())
{
    try {
        $dataDescriptor = WHMCS\Session::getAndDelete("dataDescriptor");
        $dataValue = WHMCS\Session::getAndDelete("dataValue");
        $apiUrl = net\authorize\api\constants\ANetEnvironment::PRODUCTION;
        if ($params["testMode"]) {
            $apiUrl = net\authorize\api\constants\ANetEnvironment::SANDBOX;
        }
        $returnData = array();
        WHMCS\Session::delete("cartccdetail");
        $client = WHMCS\User\Client::find($params["clientdetails"]["id"]);
        $customerProfileId = $customerPaymentProfileId = NULL;
        $gatewayId = acceptjs_findFirstCustomerToken($client);
        if ($gatewayId && is_array($gatewayId) && array_key_exists("customer", $gatewayId)) {
            $customerProfileId = $gatewayId["customer"];
        }
        if ($dataValue && $dataDescriptor) {
            $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
            $merchantAuthentication->setName($params["apiLoginId"]);
            $merchantAuthentication->setTransactionKey($params["transactionKey"]);
            $refId = "ref" . time();
            $opaqueData = new net\authorize\api\contract\v1\OpaqueDataType();
            $opaqueData->setDataDescriptor($dataDescriptor);
            $opaqueData->setDataValue($dataValue);
            $contact = $client;
            if ($client->billingContactId) {
                $contact = $client->contacts->find($client->billingContactId);
            }
            $customerAddress = new net\authorize\api\contract\v1\CustomerAddressType();
            $customerAddress->setFirstName($contact->firstName);
            $customerAddress->setLastName($contact->lastName);
            $customerAddress->setCompany($contact->companyName);
            $customerAddress->setAddress($contact->address1);
            $customerAddress->setCity($contact->city);
            $customerAddress->setState($contact->state);
            $customerAddress->setZip($contact->postcode);
            $customerAddress->setCountry($contact->countryName);
            $paymentOne = new net\authorize\api\contract\v1\PaymentType();
            $paymentOne->setOpaqueData($opaqueData);
            $paymentProfile = new net\authorize\api\contract\v1\CustomerPaymentProfileType();
            $paymentProfile->setCustomerType("individual");
            $paymentProfile->setBillTo($customerAddress);
            $paymentProfile->setPayment($paymentOne);
            $paymentProfile->setDefaultPaymentProfile(true);
            $paymentProfiles[] = $paymentProfile;
            if (!$customerProfileId) {
                $customerProfile = new net\authorize\api\contract\v1\CustomerProfileType();
                $customerProfile->setDescription($contact->fullName);
                $customerProfile->setMerchantCustomerId("M_" . time());
                $customerProfile->setEmail($contact->email);
                $customerProfile->setpaymentProfiles($paymentProfiles);
                $paymentProfileRequest = new net\authorize\api\contract\v1\CreateCustomerProfileRequest();
                $paymentProfileRequest->setMerchantAuthentication($merchantAuthentication);
                $paymentProfileRequest->setRefId($refId);
                $paymentProfileRequest->setProfile($customerProfile);
                $controller = new net\authorize\api\controller\CreateCustomerProfileController($paymentProfileRequest);
            } else {
                $paymentProfileRequest = new net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest();
                $paymentProfileRequest->setMerchantAuthentication($merchantAuthentication);
                $paymentProfileRequest->setCustomerProfileId($customerProfileId);
                $paymentProfileRequest->setPaymentProfile($paymentProfile);
                $paymentProfileRequest->setValidationMode($params["testMode"] ? "testMode" : "liveMode");
                $controller = new net\authorize\api\controller\CreateCustomerPaymentProfileController($paymentProfileRequest);
            }
            $response = $controller->executeWithApiResponse($apiUrl);
            if ($response != NULL && $response->getMessages()->getResultCode() == "Ok") {
                if (!$customerProfileId) {
                    $customerProfileId = $response->getCustomerProfileId();
                    $paymentProfiles = $response->getCustomerPaymentProfileIdList();
                    $customerPaymentProfileId = $paymentProfiles[0];
                } else {
                    $customerPaymentProfileId = $response->getCustomerPaymentProfileId();
                }
                $gatewayId = array("customer" => $customerProfileId, "payment" => $customerPaymentProfileId);
                $returnData["gatewayid"] = json_encode($gatewayId);
                $customerProfileId = $gatewayId["customer"];
                $customerPaymentProfileId = $gatewayId["payment"];
                $request = new net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest();
                $request->setMerchantAuthentication($merchantAuthentication);
                $request->setRefId($refId);
                $request->setCustomerProfileId($customerProfileId);
                $request->setCustomerPaymentProfileId($customerPaymentProfileId);
                $request->setUnmaskExpirationDate(true);
                $controller = new net\authorize\api\controller\GetCustomerPaymentProfileController($request);
                $response = $controller->executeWithApiResponse($apiUrl);
                $cardLastFour = "UNKN";
                $cardType = "Visa";
                $cardExpiry = "";
                if ($response != NULL && $response->getMessages()->getResultCode() == "Ok") {
                    $creditCard = $response->getPaymentProfile()->getPayment()->getCreditCard();
                    $cardType = $creditCard->getCardType();
                    $cardLastFour = $creditCard->getCardNumber();
                    if (4 < strlen($cardLastFour)) {
                        $cardLastFour = substr($cardLastFour, -4, 4);
                    }
                    $cardExpiry = $creditCard->getExpirationDate();
                    $cardExpiry = explode("-", $cardExpiry);
                    if (count($cardExpiry) == 2) {
                        $cardExpiry = $cardExpiry[1] . substr($cardExpiry[0], 2, 2);
                    } else {
                        $cardExpiry = "";
                    }
                    if (strtolower($cardType) == "americanexpress") {
                        $cardType = "American Express";
                    }
                }
                $returnData["cardnumber"] = $cardLastFour;
                $returnData["cardtype"] = $cardType;
                $returnData["cardexpiry"] = $cardExpiry;
            }
        }
        if (0 < $params["amount"]) {
            $merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
            $merchantAuthentication->setName($params["apiLoginId"]);
            $merchantAuthentication->setTransactionKey($params["transactionKey"]);
            $refId = "ref" . time();
            $profileToCharge = new net\authorize\api\contract\v1\CustomerProfilePaymentType();
            $profileToCharge->setCustomerProfileId($gatewayId["customer"]);
            $paymentProfile = new net\authorize\api\contract\v1\PaymentProfileType();
            $paymentProfile->setPaymentProfileId($gatewayId["payment"]);
            $profileToCharge->setPaymentProfile($paymentProfile);
            $transactionRequestType = new net\authorize\api\contract\v1\TransactionRequestType();
            $transactionRequestType->setTransactionType("authCaptureTransaction");
            $transactionRequestType->setAmount($params["amount"]);
            $transactionRequestType->setProfile($profileToCharge);
            $userFields = array();
            $userField = new net\authorize\api\contract\v1\UserFieldType();
            $userField->setName("invoice_id");
            $userField->setValue($params["invoiceid"]);
            $userFields[] = $userField;
            $transactionRequestType->setUserFields($userFields);
            if (!$params["testMode"]) {
                $transactionRequestType->setSolution((new net\authorize\api\contract\v1\SolutionType())->setId("AAA172608"));
            } else {
                $transactionRequestType->setSolution((new net\authorize\api\contract\v1\SolutionType())->setId("AAA100302"));
            }
            $request = new net\authorize\api\contract\v1\CreateTransactionRequest();
            $request->setMerchantAuthentication($merchantAuthentication);
            $request->setRefId($refId);
            $request->setTransactionRequest($transactionRequestType);
            $controller = new net\authorize\api\controller\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($apiUrl);
            if ($response != NULL) {
                if ($response->getMessages()->getResultCode() == "Ok") {
                    $tresponse = $response->getTransactionResponse();
                    if ($tresponse != NULL && $tresponse->getMessages() != NULL) {
                        $rawData = (array) $tresponse;
                        foreach ($rawData as $key => $value) {
                            unset($rawData[$key]);
                            $key = str_replace("net\\authorize\\api\\contract\\v1\\TransactionResponseType", "", $key);
                            if (is_object($value)) {
                                $value = (array) $value;
                            }
                            $rawData[$key] = $value;
                        }
                        $amount = $params["amount"];
                        if (array_key_exists("convertto", $params)) {
                            $amount = $params["basecurrencyamount"];
                        }
                        $returnData["status"] = "success";
                        $returnData["transid"] = $tresponse->getTransId();
                        $returnData["amount"] = $amount;
                        $returnData["rawdata"] = $rawData;
                    } else {
                        WHMCS\Session::set("AcceptJsDeclined" . $params["invoiceid"], true);
                        $returnData["status"] = "declined";
                        $returnData["rawdata"] = array("Error Code" => $tresponse->getErrors()[0]->getErrorCode(), "Error Message" => $tresponse->getErrors()[0]->getErrorText());
                    }
                } else {
                    $tresponse = $response->getTransactionResponse();
                    if ($tresponse != NULL && $tresponse->getErrors() != NULL) {
                        $errorCode = $tresponse->getErrors()[0]->getErrorCode();
                        $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                    } else {
                        $errorCode = $response->getMessages()->getMessage()[0]->getCode();
                        $errorMessage = $response->getMessages()->getMessage()[0]->getText();
                    }
                    $returnData["status"] = "error";
                    $returnData["rawdata"] = array("Error Code" => $errorCode, "Error Message" => $errorMessage);
                }
            }
        }
        return $returnData;
    } catch (Exception $e) {
        return array("status" => "error", "rawdata" => array("Error" => $e->getMessage()));
    }
}
function acceptjs_cc_validation(array $params = array())
{
    if (App::isInRequest("dataDescriptor")) {
        WHMCS\Session::set("dataDescriptor", (string) App::getFromRequest("dataDescriptor"));
    }
    if (App::isInRequest("dataValue")) {
        WHMCS\Session::set("dataValue", (string) App::getFromRequest("dataValue"));
    }
    return "";
}
function acceptjs_credit_card_input(array $params = array())
{
    $assetHelper = DI::make("asset");
    $now = time();
    $additional = "";
    if ($error = WHMCS\Session::getAndDelete("AcceptJsDeclined" . $params["invoiceid"])) {
        $error = Lang::trans("creditcarddeclined");
        $additional .= "\njQuery('.gateway-errors').html('" . $error . "').removeClass('hidden');";
    }
    $jsUrl = $assetHelper->getWebRoot() . "/modules/gateways/acceptjs/acceptjs.min.js?a=" . $now;
    $jqPaymentJs = WHMCS\View\Asset::jsInclude("jquery.payment.js");
    return "<script type=\"text/javascript\">\n    var clientKey = '" . $params["publicKey"] . "',\n        apiLoginId = '" . $params["apiLoginId"] . "';" . $additional . "\n</script>\n<script type=\"text/javascript\" src=\"" . $jsUrl . "\"></script>\n" . $jqPaymentJs;
}
function acceptjs_findFirstCustomerToken(WHMCS\User\Contracts\ContactInterface $client)
{
    $clientToUse = $client;
    if ($clientToUse instanceof WHMCS\User\Client\Contact) {
        $clientToUse = $clientToUse->client;
    }
    foreach ($clientToUse->payMethods as $payMethod) {
        if ($payMethod->gateway_name == "acceptjs") {
            $payment = $payMethod->payment;
            $token = json_decode($payment->getRemoteToken(), true);
            if ($token && is_array($token)) {
                return $token;
            }
        }
    }
}

?>