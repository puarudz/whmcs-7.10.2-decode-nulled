<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

function updateCCDetails($userid, $cardtype, $cardnum, $cardcvv, $cardexp, $cardstart, $cardissue, $noremotestore = "", $fullclear = "", $paymentGateway = "", &$payMethodRef = false, $ccDescription = "", $billingContactId = NULL)
{
    global $cc_encryption_hash;
    if (!$cc_encryption_hash) {
        $cc_encryption_hash = DI::make("config")["cc_encryption_hash"];
    }
    $gatewayid = get_query_val("tblclients", "gatewayid", array("id" => $userid));
    $clientModel = WHMCS\User\Client::find($userid);
    if ($fullclear && $clientModel) {
        $clientModel->deleteAllCreditCards();
    }
    $cardnum = ccFormatNumbers($cardnum);
    $cardexp = ccFormatNumbers($cardexp);
    $cardstart = ccFormatNumbers($cardstart);
    $cardissue = ccFormatNumbers($cardissue);
    $cardexp = ccFormatDate($cardexp);
    $cardstart = ccFormatDate($cardstart);
    $cardcvv = ccFormatNumbers($cardcvv);
    if ($cardtype) {
        $errormessage = checkCreditCard($cardnum, $cardtype);
        if (!$cardexp || strlen($cardexp) != 4) {
            $errormessage .= "<li>" . Lang::trans("creditcardenterexpirydate");
        } else {
            if ((int) ("20" . substr($cardexp, 2) . substr($cardexp, 0, 2)) < (int) date("Ym")) {
                $errormessage .= "<li>" . Lang::trans("creditcardexpirydateinvalid");
            }
        }
    } else {
        if ($cardnum) {
            $cardtype = getCardTypeByCardNumber($cardnum);
            $supportedCardTypes = explode(",", WHMCS\Config\Setting::getValue("AcceptedCardTypes"));
            if (!in_array($cardtype, $supportedCardTypes)) {
                $errormessage = "<li>" . Lang::trans("paymentMethodsManage.unsupportedCardType");
            }
        }
    }
    if ($errormessage) {
        return $errormessage;
    }
    if (!$userid || $noremotestore) {
        return "";
    }
    $remotestored = false;
    $ccGateways = WHMCS\Database\Capsule::table("tblpaymentgateways")->where("setting", "type")->where("value", WHMCS\Module\Gateway::GATEWAY_CREDIT_CARD)->pluck("gateway");
    if ($paymentGateway) {
        $paymentGateway = " AND `gateway` = '" . $paymentGateway . "'";
    }
    $remoteGatewayToken = "";
    $cardLastFour = "";
    $result = select_query("tblpaymentgateways", "gateway,(SELECT id FROM tblinvoices WHERE paymentmethod=gateway AND userid='" . (int) $userid . "' ORDER BY id DESC LIMIT 0,1) AS invoiceid", "setting='name'" . $paymentGateway, "order");
    while ($data = mysql_fetch_array($result)) {
        $gateway = $data["gateway"];
        if (!$gateway) {
            $gateway = getClientsPaymentMethod($userid);
        }
        if (!in_array($gateway, $ccGateways)) {
            continue;
        }
        $gatewayInterface = new WHMCS\Module\Gateway();
        $gatewayInterface->load($gateway);
        $invoiceid = $data["invoiceid"];
        $rparams = array();
        $rparams["cardtype"] = $cardtype;
        $rparams["cardnum"] = $cardnum;
        $rparams["cardcvv"] = $cardcvv;
        $rparams["cardexp"] = $cardexp;
        $rparams["cardstart"] = $cardstart;
        $rparams["cardissuenum"] = $cardissue;
        $rparams["gatewayid"] = $gatewayid;
        $action = "create";
        if ($rparams["gatewayid"]) {
            if ($rparams["cardnum"]) {
                $action = "update";
            } else {
                $action = "delete";
            }
        }
        $rparams["action"] = $action;
        if ($invoiceid) {
            $ccVariables = getCCVariables($invoiceid, "", NULL, $billingContactId);
            if ($ccVariables) {
                $rparams = array_merge($ccVariables, $rparams);
            }
        } else {
            $invoice = new WHMCS\Invoice();
            $rparams = array_merge($invoice->initialiseGatewayAndParams($gateway), $rparams);
            $client = new WHMCS\Client($userid);
            $clientsdetails = $client->getDetails("billing");
            $clientsdetails["state"] = $clientsdetails["statecode"];
            $rparams["clientdetails"] = $clientsdetails;
        }
        if ($gatewayInterface->functionExists("storeremote")) {
            $captureresult = $gatewayInterface->call("storeremote", $rparams);
            $debugdata = is_array($captureresult["rawdata"]) ? array_merge(array("UserID" => $rparams["clientdetails"]["userid"]), $captureresult["rawdata"]) : "UserID => " . $rparams["clientdetails"]["userid"] . "\n" . $captureresult["rawdata"];
            if ($captureresult["status"] == "success") {
                if (isset($captureresult["gatewayid"])) {
                    $remoteGatewayToken = $captureresult["gatewayid"];
                }
                if (array_key_exists("cardtype", $captureresult) && $captureresult["cardtype"]) {
                    $cardtype = $captureresult["cardtype"];
                }
                if (array_key_exists("cardnumber", $captureresult) && $captureresult["cardnumber"]) {
                    $cardnum = $captureresult["cardnumber"];
                }
                if (array_key_exists("cardlastfour", $captureresult) && $captureresult["cardlastfour"]) {
                    $cardLastFour = $captureresult["cardlastfour"];
                }
                if (array_key_exists("cardexpiry", $captureresult) && $captureresult["cardexpiry"]) {
                    $cardexp = $captureresult["cardexpiry"];
                }
                if ($action == "delete" && !(array_key_exists("nodelete", $captureresult) && $captureresult["nodelete"])) {
                    update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "startdate" => "", "issuenumber" => "", "gatewayid" => ""), array("id" => $userid));
                }
                logTransaction($gateway, $debugdata, "Remote Storage Success");
                $remotestored = true;
                break;
            }
            logTransaction($gateway, $debugdata, "Remote Storage " . ucfirst($captureresult["status"]));
            return "<li>" . Lang::trans("remoteTransError");
        }
    }
    if (WHMCS\Config\Setting::getValue("CCNeverStore") && !$remotestored) {
        return "";
    }
    if (!$cardtype && !$cardnum && !$cardexp) {
        return "";
    }
    $gatewayObject = $isRefGateway = NULL;
    if ($gateway) {
        $gatewayObject = WHMCS\Module\Gateway::factory($gateway);
        if ($gatewayObject && $gatewayObject->getWorkflowType() === WHMCS\Module\Gateway::WORKFLOW_TOKEN) {
            $isRefGateway = true;
        }
    }
    if ($remotestored) {
        $payMethodClass = "WHMCS\\Payment\\PayMethod\\Adapter\\RemoteCreditCard";
        if ($cardnum) {
            $cardLastFour = substr($cardnum, -4, 4);
        }
        $cardnum = "";
    } else {
        if ($isRefGateway) {
            $payMethodClass = "WHMCS\\Payment\\PayMethod\\Adapter\\RemoteCreditCard";
        } else {
            $payMethodClass = "WHMCS\\Payment\\PayMethod\\Adapter\\CreditCard";
        }
    }
    if ($remotestored || $cardnum) {
        if (!$clientModel) {
            return "";
        }
        if ($payMethodRef) {
            $payMethod = $payMethodRef;
        } else {
            $payMethod = $payMethodClass::factoryPayMethod($clientModel, $clientModel, $ccDescription);
        }
        $payment = $payMethod->payment;
        if ($cardnum) {
            $payment->setCardNumber($cardnum);
        }
        if ($cardLastFour) {
            $payment->setLastFour($cardLastFour);
        }
        if ($cardtype) {
            $payment->setCardType($cardtype);
        }
        if ($cardstart) {
            $payment->setStartDate(WHMCS\Carbon::createFromCcInput($cardstart));
        }
        if ($cardexp) {
            try {
                $payment->setExpiryDate(WHMCS\Carbon::createFromCcInput($cardexp));
            } catch (Exception $e) {
                $payment->setExpiryDate(WHMCS\Carbon::today()->endOfMonth()->endOfDay());
            }
        }
        if ($cardissue) {
            $payment->setIssueNumber($cardissue);
        }
        if ($remotestored && $remoteGatewayToken) {
            $payment->setRemoteToken($remoteGatewayToken);
            $payMethod->setGateway($gatewayObject);
        } else {
            if ($isRefGateway) {
                $payMethod->setGateway($gatewayObject);
            }
        }
        if ($gatewayObject && $gatewayObject->isTokenised() && !$gatewayObject->functionExists("storeremote") && $payment instanceof WHMCS\Payment\PayMethod\Adapter\CreditCard) {
            $payment->setIsMigrating(true);
        }
        $payment->validateRequiredValuesPreSave()->save();
        $payMethod->save();
        if ($payMethodRef !== false) {
            $payMethodRef = $payMethod;
        }
    }
    logActivity("Updated Stored Credit Card Details - User ID: " . $userid, $userid);
    run_hook("CCUpdate", array("userid" => $userid, "cardtype" => $cardtype, "cardnum" => $cardnum, "cardcvv" => $cardcvv, "expdate" => $cardexp, "cardstart" => $cardstart, "issuenumber" => $cardissue));
}
function ccFormatNumbers($val)
{
    return preg_replace("/[^0-9]/", "", $val);
}
function ccFormatDate($date)
{
    if (strlen($date) == 3) {
        $date = "0" . $date;
    }
    if (strlen($date) == 5) {
        $date = "0" . $date;
    }
    if (strlen($date) == 6) {
        $date = substr($date, 0, 2) . substr($date, -2);
    }
    return $date;
}
function getClientDefaultCardDetails($userId, $mode = "allowLegacy", $paymentModule = NULL)
{
    $cardDetails = array("cardtype" => NULL, "cardlastfour" => NULL, "cardnum" => Lang::trans("nocarddetails"), "fullcardnum" => NULL, "expdate" => "", "startdate" => "", "issuenumber" => NULL, "gatewayid" => NULL);
    try {
        $client = WHMCS\User\Client::findOrFail($userId);
        if (!in_array($mode, array("forceLegacy", "forcePayMethod", "allowLegacy"))) {
            $mode = "allowLegacy";
        }
        if ($mode == "forceLegacy") {
            return getCCDetails($userId);
        }
        if ($mode == "allowLegacy" && ($client->needsCardDetailsMigrated() || $client->needsUnknownPaymentTokenMigrated())) {
            return getCCDetails($userId);
        }
        $payMethods = $client->payMethods->creditCards();
        if ($paymentModule) {
            $payMethods = $payMethods->forGateway($paymentModule);
        }
        $gateway = new WHMCS\Module\Gateway();
        $payMethod = NULL;
        foreach ($payMethods as $tryPayMethod) {
            if (!$tryPayMethod->isUsingInactiveGateway()) {
                $payMethod = $tryPayMethod;
                break;
            }
        }
        $cardDetails = getPayMethodCardDetails($payMethod);
        if ($payMethod) {
            $cardDetails["payMethod"] = $payMethod;
        }
    } catch (Exception $e) {
    }
    return $cardDetails;
}
function getCCDetails($userid)
{
    $config = DI::make("config");
    $cc_encryption_hash = $config["cc_encryption_hash"];
    $cchash = md5($cc_encryption_hash . $userid);
    $result = select_query("tblclients", "cardtype,cardlastfour,AES_DECRYPT(cardnum,'" . $cchash . "') as cardnum,AES_DECRYPT(expdate,'" . $cchash . "') as expdate,AES_DECRYPT(issuenumber,'" . $cchash . "') as issuenumber,AES_DECRYPT(startdate,'" . $cchash . "') as startdate,gatewayid,billingcid", array("id" => $userid));
    $data = mysql_fetch_array($result);
    $carddata = array();
    $carddata["cardtype"] = $data["cardtype"];
    $carddata["cardlastfour"] = $data["cardlastfour"];
    $carddata["cardnum"] = $data["cardlastfour"] ? "************" . $data["cardlastfour"] : Lang::trans("nocarddetails");
    $carddata["fullcardnum"] = $data["cardnum"];
    $carddata["expdate"] = $data["expdate"] ? substr($data["expdate"], 0, 2) . "/" . substr($data["expdate"], 2, 2) : "";
    $carddata["startdate"] = $data["startdate"] ? substr($data["startdate"], 0, 2) . "/" . substr($data["startdate"], 2, 2) : "";
    $carddata["issuenumber"] = $data["issuenumber"];
    $carddata["gatewayid"] = $data["gatewayid"];
    $carddata["billingcontactid"] = $data["billingcid"];
    $carddata["payMethod"] = NULL;
    return $carddata;
}
function getCCVariables($invoiceid, $gatewayName = "", WHMCS\Payment\PayMethod\Model $payMethod = NULL, $billingContactId = NULL)
{
    try {
        $invoice = new WHMCS\Invoice($invoiceid);
    } catch (Exception $e) {
        return array();
    }
    $userid = $invoice->getData("userid");
    if (!$payMethod) {
        $invoiceModel = $invoice->getModel();
        if ($invoiceModel instanceof WHMCS\Billing\Invoice && $invoiceModel->payMethod && !$invoiceModel->payMethod->trashed()) {
            $payMethod = $invoiceModel->payMethod;
        }
    }
    if ($payMethod) {
        $data = getPayMethodCardDetails($payMethod);
    } else {
        $data = getclientdefaultcarddetails($userid, "allowLegacy", $invoice->getData("paymentmodule"));
    }
    $cardtype = $data["cardtype"];
    $cardnum = $data["fullcardnum"];
    $cardexp = str_replace("/", "", $data["expdate"]);
    $startdate = str_replace("/", "", $data["startdate"]);
    $issuenumber = $data["issuenumber"];
    $gatewayid = $data["gatewayid"];
    if (!$payMethod && $data["payMethod"]) {
        $payMethod = $data["payMethod"];
    }
    if (!function_exists("getClientDefaultBankDetails")) {
        include_once ROOTDIR . "/includes/clientfunctions.php";
    }
    if ($payMethod && ($payMethod->isBankAccount() || $payMethod->isRemoteBankAccount())) {
        $data = getPayMethodBankDetails($payMethod);
    } else {
        $data = getClientDefaultBankDetails($userid);
    }
    $bankname = $data["bankname"];
    $banktype = $data["banktype"];
    $bankcode = $data["bankcode"];
    $bankacct = $data["bankacct"];
    if (!$payMethod && $data["payMethod"]) {
        $payMethod = $data["payMethod"];
    }
    if ($payMethod && $payMethod->isRemoteBankAccount()) {
        $gatewayid = $data["gatewayid"];
    }
    try {
        if ($gatewayName) {
            $params = $invoice->initialiseGatewayAndParams($gatewayName);
        } else {
            $params = $invoice->initialiseGatewayAndParams();
        }
    } catch (Exception $e) {
        logActivity("Failed to initialise payment gateway module: " . $e->getMessage());
        throw new WHMCS\Exception\Fatal("Could not initialise payment gateway. Please contact support.");
    }
    if (isset($billingContactId)) {
        $params["billingcontactid"] = $billingContactId;
    }
    $params = array_merge($params, $invoice->getGatewayInvoiceParams($params));
    $params["cardtype"] = $cardtype;
    $params["cardnum"] = $cardnum;
    $params["cardexp"] = $cardexp;
    $params["cardstart"] = $startdate;
    $params["cardissuenum"] = $issuenumber;
    if ($banktype) {
        $params["bankname"] = $bankname;
        $params["banktype"] = $banktype;
        $params["bankcode"] = $bankcode;
        $params["bankacct"] = $bankacct;
    }
    $params["disableautocc"] = $params["clientdetails"]["disableautocc"];
    $params["gatewayid"] = $gatewayid;
    if ($payMethod) {
        $params["payMethod"] = $payMethod;
    }
    return $params;
}
function captureCCPayment($invoiceid, $cccvv = "", $passedparams = false, WHMCS\Payment\PayMethod\Model $payMethod = NULL)
{
    global $params;
    $gateway = NULL;
    if (!$passedparams) {
        $gatewayName = "";
        if ($payMethod) {
            $gateway = $payMethod->getGateway();
            if ($gateway) {
                $params["paymentmethod"] = $gateway->getLoadedModule();
                $gatewayName = $params["paymentmethod"];
            }
        }
        $params = getccvariables($invoiceid, $gatewayName, $payMethod);
        if (!$payMethod && $params["payMethod"] instanceof WHMCS\Payment\PayMethod\Model) {
            $payMethod = $params["payMethod"];
        }
    }
    if ($cccvv) {
        $params["cccvv"] = $cccvv;
    }
    $returnState = false;
    $invoiceModel = WHMCS\Billing\Invoice::find($invoiceid);
    if (!$invoiceModel) {
        return $returnState;
    }
    if ($payMethod) {
        $invoiceModel->payMethod()->associate($payMethod);
        $invoiceModel->save();
    }
    if (is_null($gateway)) {
        $gateway = new WHMCS\Module\Gateway();
        $gateway->load($params["paymentmethod"]);
    }
    if ($gateway->getProcessingType() != WHMCS\Module\Gateway::PROCESSING_OFFLINE) {
        if ($params["amount"] <= 0) {
            logTransaction($params["paymentmethod"], "", "No Amount Due");
        } else {
            if (!$params["cardnum"] && !$params["gatewayid"] && !$params["cccvv"] && !$params["bankacct"]) {
                $history = WHMCS\Billing\Payment\Transaction\History::firstOrNew(array("invoice_id" => $invoiceid, "transaction_id" => "N/A", "gateway" => $gateway->getDisplayName()));
                $history->description = "No Pay Method Available";
                $history->amount = $params["amount"];
                $history->currencyId = $params["currency"];
                $history->save();
                sendMessage("Credit Card Payment Due", $invoiceid);
            } else {
                try {
                    $captureresult = $gateway->call("capture", $params);
                } catch (Exception $e) {
                    $captureresult = array("status" => "error", "rawdata" => "Payment capture error: " . $e->getMessage());
                }
                $invoiceModel = WHMCS\Billing\Invoice::find($invoiceid);
                if (!$invoiceModel) {
                    return false;
                }
                $invoiceModel->lastCaptureAttempt = WHMCS\Carbon::now();
                $checkTransactionId = "N/A";
                if (is_array($captureresult) && !empty($captureresult["transid"])) {
                    $checkTransactionId = $captureresult["transid"];
                }
                $history = WHMCS\Billing\Payment\Transaction\History::firstOrNew(array("invoice_id" => $invoiceid, "transaction_id" => $checkTransactionId, "gateway" => $gateway->getDisplayName()));
                $history->description = "Automatic Payment Attempt";
                $history->amount = $params["amount"];
                $history->currencyId = $params["currency"];
                if (!$history->exists) {
                    $history->save();
                }
                if (is_array($captureresult)) {
                    logTransaction($params["paymentmethod"], $captureresult["rawdata"], ucfirst($captureresult["status"]), array("history_id" => $history->id));
                    $history->remoteStatus = ucfirst($captureresult["status"]);
                    $emailExtra = array("payMethod" => NULL);
                    if (in_array($captureresult["status"], array("success", "pending"))) {
                        $remoteGatewayToken = NULL;
                        $cardType = $cardNumber = $cardExpiry = NULL;
                        if (isset($captureresult["gatewayid"])) {
                            $remoteGatewayToken = $captureresult["gatewayid"];
                        }
                        if ($remoteGatewayToken && $payMethod) {
                            if ($payMethod->isCreditCard()) {
                                $invoiceModel->convertLocalCardToRemote($remoteGatewayToken);
                            } else {
                                $invoiceModel->convertLocalBankAccountToRemote($remoteGatewayToken);
                            }
                            $payMethod = $invoiceModel->payMethod;
                            if ($cardNumber) {
                                $payMethod->payment->setCardNumber($cardNumber);
                            }
                            if ($cardExpiry) {
                                if (!$cardExpiry instanceof WHMCS\Carbon) {
                                    $cardExpiry = WHMCS\Carbon::createFromCcInput($cardExpiry);
                                }
                                $payMethod->payment->setExpiryDate($cardExpiry);
                            }
                            if ($cardType) {
                                $payMethod->payment->setCardType($cardType);
                            }
                            $payMethod->payment->save();
                            $payMethod->save();
                        } else {
                            if ($remoteGatewayToken) {
                                $client = $invoiceModel->client;
                                $client->paymentGatewayToken = $remoteGatewayToken;
                                if ($cardNumber) {
                                    $client->cardnum = $client->generateCreditCardEncryptedField($cardNumber);
                                    $client->creditCardLastFourDigits = substr($cardNumber, -4);
                                }
                                if ($cardExpiry) {
                                    $client->creditCardExpiryDate = $client->generateCreditCardEncryptedField($cardExpiry);
                                }
                                if ($cardType) {
                                    $client->creditCardType = $cardType;
                                }
                                $client->save();
                            }
                        }
                        $emailExtra["payMethod"] = $payMethod;
                    }
                    if ($captureresult["status"] == "success") {
                        $emailTemplate = "Credit Card Payment Confirmation";
                        if ($customEmailTemplate = $gateway->getMetaDataValue("successEmail")) {
                            $customEmailTemplate = WHMCS\Mail\Template::where("name", "=", $customEmailTemplate)->first();
                            if ($customEmailTemplate) {
                                $emailTemplate = $customEmailTemplate->name;
                            }
                        }
                        $invoiceModel->addPayment($invoiceModel->balance, $captureresult["transid"], $captureresult["fee"], $gateway->getLoadedModule(), true);
                        sendMessage($emailTemplate, $params["invoiceid"], $emailExtra);
                        $returnState = true;
                        $history->transactionId = $captureresult["transid"];
                        $history->completed = true;
                    } else {
                        if ($captureresult["status"] == "pending") {
                            $emailTemplate = "Credit Card Payment Pending";
                            if ($customEmailTemplate = $gateway->getMetaDataValue("pendingEmail")) {
                                $customEmailTemplate = WHMCS\Mail\Template::where("name", "=", $customEmailTemplate)->first();
                                if ($customEmailTemplate) {
                                    $emailTemplate = $customEmailTemplate->name;
                                }
                            }
                            $invoiceModel->status = "Payment Pending";
                            sendMessage($emailTemplate, $params["invoiceid"], $emailExtra);
                            $returnState = "pending";
                        } else {
                            if (array_key_exists("declinereason", $captureresult)) {
                                $history->description = $captureresult["declinereason"];
                            } else {
                                $history->description = "Payment Failed";
                            }
                            $emailTemplate = "Credit Card Payment Failed";
                            if ($customEmailTemplate = $gateway->getMetaDataValue("failedEmail")) {
                                $customEmailTemplate = WHMCS\Mail\Template::where("name", "=", $customEmailTemplate)->first();
                                if ($customEmailTemplate) {
                                    $emailTemplate = $customEmailTemplate->name;
                                }
                            }
                            sendMessage($emailTemplate, $params["invoiceid"]);
                        }
                    }
                } else {
                    if ($captureresult == "success") {
                        $returnState = true;
                        $history->completed = true;
                    }
                }
                $history->save();
                if ($returnState && $payMethod) {
                    $invoiceModel->payMethod()->associate($payMethod);
                }
                $invoiceModel->save();
            }
        }
    }
    return $returnState;
}
function ccProcessing(WHMCS\Scheduling\Task\TaskInterface $task = NULL)
{
    $today = WHMCS\Carbon::today();
    $processingDays = (int) WHMCS\Config\Setting::getValue("CCProcessDaysBefore");
    WHMCS\Payment\PayMethod\Model::deleteExpiredCreditCards($task);
    $chargedate = $today->addDays($processingDays)->toDateString();
    $chargedates = array();
    if (!WHMCS\Config\Setting::getValue("CCAttemptOnlyOnce")) {
        for ($i = 1; $i <= WHMCS\Config\Setting::getValue("CCRetryEveryWeekFor"); $i++) {
            $chargedates[] = "tblinvoices.duedate='" . $today->subDays($i * 7)->toDateString() . "'";
        }
    }
    $qrygateways = array();
    $compatibleGateways = WHMCS\Database\Capsule::table("tblpaymentgateways")->where("setting", "=", "type")->whereIn("value", array(WHMCS\Module\Gateway::GATEWAY_CREDIT_CARD, WHMCS\Module\Gateway::GATEWAY_BANK))->pluck("gateway");
    foreach ($compatibleGateways as $compatibleGatewayName) {
        $qrygateways[] = "tblinvoices.paymentmethod='" . db_escape_string($compatibleGatewayName) . "'";
    }
    if (count($qrygateways)) {
        $z = $y = 0;
        $query = "SELECT tblinvoices.* FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE (tblinvoices.status='Unpaid') AND (" . implode(" OR ", $qrygateways) . ") AND tblclients.disableautocc='' AND (tblinvoices.duedate='" . $chargedate . "'";
        if (!WHMCS\Config\Setting::getValue("CCAttemptOnlyOnce")) {
            if (0 < count($chargedates)) {
                $query .= " OR " . implode(" OR ", $chargedates);
            } else {
                $query .= " OR tblinvoices.duedate<'" . $chargedate . "'";
            }
        }
        $query .= ")";
        $result = full_query($query);
        while ($data = mysql_fetch_array($result)) {
            if (!$task) {
                logActivity("Processing Capture for Invoice ID: " . $data["id"], $data["userid"]);
            }
            $ccResult = captureccpayment($data["id"]);
            if (is_string($ccResult) && $ccResult == "success" || is_string($ccResult) && $ccResult == "pending" || is_bool($ccResult) && $ccResult) {
                if ($task) {
                    $task->addSuccess(array("invoice", $data["id"]));
                }
                if (!$task) {
                    $z++;
                    logActivity("Capture Successful for Invoice ID: " . $data["id"], $data["userid"]);
                }
            } else {
                if ($task) {
                    $task->addFailure(array("invoice", $data["id"], ""));
                }
                if (!$task) {
                    $y++;
                    logActivity("Capture Failed for Invoice ID: " . $data["id"], $data["userid"]);
                }
            }
        }
        if ($task) {
            $task->output("captured")->write(count($task->getSuccesses()));
            $task->output("failures")->write(count($task->getFailures()));
            $task->output("action.detail")->write(json_encode($task->getDetail()));
        } else {
            logActivity("Credit Card Payments Processed (" . $z . " Captured, " . $y . " Failed)");
        }
        return (string) $z . " Captured, " . $y . " Failed";
    }
    return false;
}
function checkCreditCard($cardnumber, $cardname)
{
    global $_LANG;
    $cards = array(array("name" => "Visa", "length" => "13,16", "prefixes" => "4", "checkdigit" => true), array("name" => "MasterCard", "length" => "16", "prefixes" => "51,52,53,54,55,22,23,24,25,26,270,271,2720", "checkdigit" => true), array("name" => "Diners Club", "length" => "14", "prefixes" => "300,301,302,303,304,305,36,38,39", "checkdigit" => true), array("name" => "American Express", "length" => "15", "prefixes" => "34,37", "checkdigit" => true), array("name" => "Discover", "length" => "16", "prefixes" => "6011,64,65,622", "checkdigit" => true), array("name" => "JCB", "length" => "15,16", "prefixes" => "3,1800,2131", "checkdigit" => true), array("name" => "Forbrugsforeningen", "length" => "16", "prefixes" => "600", "checkdigit" => true), array("name" => "Dankort", "length" => "16", "prefixes" => "5019", "checkdigit" => true), array("name" => "Maestro", "length" => "12,13,14,15,16,17,18,19", "prefixes" => "5018,502,503,506,56,58,639,6220,67", "checkdigit" => true), array("name" => "UnionPay", "length" => "16", "prefixes" => "35", "checkdigit" => false));
    $cardType = -1;
    for ($i = 0; $i < sizeof($cards); $i++) {
        if (strtolower($cardname) == strtolower($cards[$i]["name"])) {
            $cardType = $i;
            break;
        }
    }
    if (strlen($cardnumber) == 0) {
        return "<li>" . $_LANG["creditcardenternumber"];
    }
    if ($cards[$cardType]) {
        $cardNo = $cardnumber;
        if ($cards[$cardType]["checkdigit"]) {
            $checksum = 0;
            $mychar = "";
            $j = 1;
            for ($i = strlen($cardNo) - 1; 0 <= $i; $i--) {
                $calc = $cardNo[$i] * $j;
                if (9 < $calc) {
                    $checksum = $checksum + 1;
                    $calc = $calc - 10;
                }
                $checksum = $checksum + $calc;
                if ($j == 1) {
                    $j = 2;
                } else {
                    $j = 1;
                }
            }
            if ($checksum % 10 != 0) {
                return "<li>" . $_LANG["creditcardnumberinvalid"];
            }
        }
        $prefixes = explode(",", $cards[$cardType]["prefixes"]);
        $PrefixValid = false;
        foreach ($prefixes as $prefix) {
            if (substr($cardNo, 0, strlen($prefix)) == $prefix) {
                $PrefixValid = true;
                break;
            }
        }
        if (!$PrefixValid) {
            return "<li>" . $_LANG["creditcardnumberinvalid"];
        }
        $LengthValid = false;
        $lengths = explode(",", $cards[$cardType]["length"]);
        foreach ($lengths as $length) {
            if (strlen($cardNo) == $length) {
                $LengthValid = true;
                break;
            }
        }
        if (!$LengthValid) {
            return "<li>" . $_LANG["creditcardnumberinvalid"];
        }
    }
}
function getCardTypeByCardNumber($cardNumber)
{
    $cardNumber = preg_replace("/[^0-9]/", "", $cardNumber);
    switch (true) {
        case substr($cardNumber, 0, 3) == "300" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 3) == "301" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 3) == "302" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 3) == "303" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 3) == "304" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 3) == "305" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 2) == "36" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 2) == "38" && strlen($cardNumber) == 14:
        case substr($cardNumber, 0, 2) == "39" && strlen($cardNumber) == 14:
            return "Diners Club";
        case substr($cardNumber, 0, 2) == "34" && strlen($cardNumber) == 15:
        case substr($cardNumber, 0, 2) == "37" && strlen($cardNumber) == 15:
            return "American Express";
        case substr($cardNumber, 0, 4) == "6011" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "64" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "65" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 3) == "622" && strlen($cardNumber) == 16:
            return "Discover";
        case substr($cardNumber, 0, 1) == "4" && strlen($cardNumber) == 13:
        case substr($cardNumber, 0, 1) == "4" && strlen($cardNumber) == 16:
            return "Visa";
        case substr($cardNumber, 0, 2) == "51" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "52" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "53" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "54" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "55" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "22" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "23" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "24" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "25" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 2) == "26" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 3) == "270" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 3) == "271" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 4) == "2720" && strlen($cardNumber) == 16:
            return "MasterCard";
        case substr($cardNumber, 0, 1) == "3" && strlen($cardNumber) == 15:
        case substr($cardNumber, 0, 1) == "3" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 4) == "1800" && strlen($cardNumber) == 15:
        case substr($cardNumber, 0, 4) == "1800" && strlen($cardNumber) == 16:
        case substr($cardNumber, 0, 4) == "2131" && strlen($cardNumber) == 15:
        case substr($cardNumber, 0, 4) == "2131" && strlen($cardNumber) == 16:
            return "JCB";
        case substr($cardNumber, 0, 4) == "5018" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 4) == "6220" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 3) == "502" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 3) == "503" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 3) == "506" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 3) == "639" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 2) == "56" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 2) == "58" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
        case substr($cardNumber, 0, 2) == "67" && in_array(strlen($cardNumber), array(12, 13, 14, 15, 16, 17, 18, 19)):
            return "Maestro";
        case substr($cardNumber, 0, 2) == "62" && in_array(strlen($cardNumber), array(16, 17, 18, 19)):
        case substr($cardNumber, 0, 2) == "88" && in_array(strlen($cardNumber), array(16, 17, 18, 19)):
            return "UnionPay";
        case substr($cardNumber, 0, 3) == "600" && strlen($cardNumber) == 16:
            return "Forbrugsforeningen";
        case substr($cardNumber, 0, 4) == "5019" && strlen($cardNumber) == 16:
            return "Dankort";
    }
    return "Card";
}
function getPayMethodCardDetails(WHMCS\Payment\PayMethod\Model $payMethod = NULL)
{
    $cardDetails = array("cardtype" => NULL, "cardlastfour" => NULL, "cardnum" => Lang::trans("nocarddetails"), "fullcardnum" => NULL, "expdate" => "", "startdate" => "", "issuenumber" => NULL, "gatewayid" => NULL, "billingcontactid" => NULL, "payMethod" => $payMethod);
    try {
        if (!$payMethod || !$payMethod->isCreditCard()) {
            throw new WHMCS\Payment\Exception\InvalidModuleException("Not a Credit Card");
        }
        $payment = $payMethod->payment;
        $cardDetails["paymethodid"] = $payMethod->id;
        $cardDetails["card_description"] = $payMethod->getDescription();
        $cardDetails["cardtype"] = $payment->getCardType();
        $cardDetails["cardlastfour"] = $payment->getLastFour();
        $cardDetails["cardnum"] = $payment->getMaskedCardNumber();
        $cardDetails["fullcardnum"] = $payment->getCardNumber();
        $cardDetails["billingcontactid"] = $payMethod->getContactId();
        $expiry = $payment->getExpiryDate();
        if ($expiry) {
            $cardDetails["expdate"] = $expiry->toCreditCard();
        }
        $startDate = $payment->getStartDate();
        if ($startDate) {
            $cardDetails["startdate"] = $startDate->toCreditCard();
        }
        $cardDetails["issuenumber"] = $payment->getIssueNumber();
        if ($payment instanceof WHMCS\Payment\PayMethod\Adapter\RemoteCreditCard) {
            $cardDetails["gatewayid"] = $payment->getRemoteToken();
        }
    } catch (Exception $e) {
    }
    return $cardDetails;
}
function getPayMethodBankDetails(WHMCS\Payment\PayMethod\Model $payMethod = NULL)
{
    $bankDetails = array("bankname" => NULL, "banktype" => NULL, "bankacct" => NULL, "bankcode" => NULL, "gatewayid" => NULL, "billingcontactid" => NULL, "payMethod" => $payMethod);
    try {
        if (!$payMethod || !$payMethod->isBankAccount() && !$payMethod->isRemoteBankAccount()) {
            throw new WHMCS\Payment\Exception\InvalidModuleException("Not a Bank Account");
        }
        $payment = $payMethod->payment;
        $bankDetails["paymethodid"] = $payMethod->id;
        if ($payment instanceof WHMCS\Payment\PayMethod\Adapter\BankAccount) {
            $bankDetails["bankname"] = $payment->getBankName();
            $bankDetails["banktype"] = $payment->getAccountType();
            $bankDetails["bankacct"] = $payment->getAccountNumber();
            $bankDetails["bankcode"] = $payment->getRoutingNumber();
        }
        $bankDetails["billingcontactid"] = $payMethod->getContactId();
        if ($payment instanceof WHMCS\Payment\PayMethod\Adapter\RemoteBankAccount) {
            $bankDetails["bankacct"] = $payment->getAccountNumber();
            $bankDetails["gatewayid"] = $payment->getRemoteToken();
        }
    } catch (Exception $e) {
    }
    return $bankDetails;
}
function saveNewRemoteCardDetails(array $remoteDetails, WHMCS\Module\Gateway $gateway, $clientId)
{
    $defaults = array("cardtype" => "", "cardnumber" => "XXXX", "cardexpiry" => WHMCS\Carbon::today()->endOfMonth()->endOfDay());
    $client = WHMCS\User\Client::find($clientId);
    $billingContact = $client;
    if ($client->billingContactId) {
        $billingContact = $client->contacts->find($client->billingContactId);
    }
    $remoteDetails = array_merge($defaults, $remoteDetails);
    $description = "";
    if (isset($remoteDetails["description"])) {
        $description = $remoteDetails["description"];
    }
    $remoteToken = (string) $remoteDetails["gatewayid"];
    $cardType = $remoteDetails["cardtype"];
    $expiryDate = $remoteDetails["cardexpiry"];
    if (!$expiryDate instanceof WHMCS\Carbon) {
        $expiryDate = WHMCS\Carbon::createFromCcInput($expiryDate);
    }
    $cardNumber = $remoteDetails["cardnumber"];
    $payMethod = WHMCS\Payment\PayMethod\Adapter\RemoteCreditCard::factoryPayMethod($client, $billingContact, $description);
    $payment = $payMethod->payment;
    $payMethod->setGateway($gateway);
    $payment->setCardNumber($cardNumber);
    if ($cardType) {
        $payment->setCardType($cardType);
    }
    $payment->setExpiryDate($expiryDate);
    $payment->setRemoteToken($remoteToken);
    $payment->save();
    $payMethod->save();
    return $payMethod;
}

?>