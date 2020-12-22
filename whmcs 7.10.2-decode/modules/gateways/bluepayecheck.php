<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

function bluepayecheck_MetaData()
{
    return array("gatewayType" => WHMCS\Module\Gateway::GATEWAY_BANK, "failedEmail" => "Direct Debit Payment Failed", "successEmail" => "Direct Debit Payment Confirmation", "pendingEmail" => "Direct Debit Payment Pending");
}
function bluepayecheck_config()
{
    $configarray = array("FriendlyName" => array("Type" => "System", "Value" => "BluePay Echeck"), "bpaccountid" => array("FriendlyName" => "Account ID", "Type" => "text", "Size" => "20"), "bpuserid" => array("FriendlyName" => "User ID", "Type" => "text", "Size" => "20"), "bpsecretkey" => array("FriendlyName" => "Secret Key", "Type" => "text", "Size" => "30"), "testmode" => array("FriendlyName" => "Test Module", "Type" => "yesno"));
    return $configarray;
}
function bluepayecheck_nolocalcc()
{
}
function bluepayecheck_capture($params)
{
    $url = "https://secure.bluepay.com/interfaces/bp20post";
    $postfields = array();
    $postfields["ACCOUNT_ID"] = $params["bpaccountid"];
    $postfields["USER_ID"] = $params["bpuserid"];
    $postfields["TRANS_TYPE"] = "SALE";
    $postfields["PAYMENT_TYPE"] = "ACH";
    $postfields["MODE"] = $params["testmode"] ? "TEST" : "LIVE";
    $postfields["AMOUNT"] = $params["amount"];
    $postfields["INVOICE_ID"] = $params["invoiceid"];
    $postfields["NAME1"] = $params["clientdetails"]["firstname"];
    $postfields["NAME2"] = $params["clientdetails"]["lastname"];
    $postfields["COMPANY_NAME"] = $params["clientdetails"]["companyname"];
    $postfields["ADDR1"] = $params["clientdetails"]["address1"];
    $postfields["ADDR2"] = $params["clientdetails"]["address2"];
    $postfields["CITY"] = $params["clientdetails"]["city"];
    $postfields["STATE"] = $params["clientdetails"]["state"];
    $postfields["ZIP"] = $params["clientdetails"]["postcode"];
    $postfields["COUNTRY"] = $params["clientdetails"]["country"];
    $postfields["PHONE"] = $params["clientdetails"]["phonenumber"];
    $postfields["EMAIL"] = $params["clientdetails"]["email"];
    if (array_key_exists("bankcode", $params) && $params["bankcode"]) {
        $postfields["PAYMENT_ACCOUNT"] = strtoupper(substr($params["banktype"], 0, 1)) . ":" . $params["bankcode"] . ":" . $params["bankacct"];
        $postfields["DOC_TYPE"] = "WEB";
        $postfields["TAMPER_PROOF_SEAL"] = md5($params["bpsecretkey"] . $params["bpaccountid"] . $postfields["TRANS_TYPE"] . $postfields["AMOUNT"] . $postfields["MASTER_ID"] . $postfields["NAME1"] . $postfields["PAYMENT_ACCOUNT"]);
    } else {
        if (array_key_exists("gatewayid", $params) && $params["gatewayid"]) {
            $postfields["MASTER_ID"] = $params["gatewayid"];
            $postfields["TAMPER_PROOF_SEAL"] = md5($params["bpsecretkey"] . $params["bpaccountid"] . $postfields["TRANS_TYPE"] . $postfields["AMOUNT"] . $postfields["MASTER_ID"] . $postfields["NAME1"] . $postfields["PAYMENT_ACCOUNT"]);
        } else {
            return array("status" => "error", "declinereason" => "No bank details or remote token", "rawdata" => $postfields);
        }
    }
    $resultarray = array();
    try {
        $data = curlCall($url, $postfields);
        $result = explode("&", $data);
        foreach ($result as $res) {
            $res = explode("=", $res);
            $resultarray[$res[0]] = $res[1];
        }
    } catch (Exception $e) {
        $resultarray["MESSAGE"] = $e->getMessage();
    }
    if ($resultarray["STATUS"] == "1") {
        $returnData = array("status" => "success", "transid" => $resultarray["TRANS_ID"], "gatewayid" => $resultarray["TRANS_ID"], "rawdata" => $resultarray);
        return $returnData;
    }
    return array("status" => "error", "rawdata" => $resultarray, "declinereason" => $resultarray["MESSAGE"]);
}

?>