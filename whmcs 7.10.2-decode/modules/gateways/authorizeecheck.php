<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

function authorizeecheck_config()
{
    return array("FriendlyName" => array("Type" => "System", "Value" => "Authorize.net Echeck"), "loginid" => array("FriendlyName" => "Login ID", "Type" => "text", "Size" => "20"), "transkey" => array("FriendlyName" => "Transaction Key", "Type" => "password", "Size" => "20"), "testMode" => array("FriendlyName" => "Test Mode", "Type" => "yesno"));
}
function authorizeecheck_MetaData()
{
    return array("gatewayType" => WHMCS\Module\Gateway::GATEWAY_BANK, "failedEmail" => "Direct Debit Payment Failed", "successEmail" => "Direct Debit Payment Confirmation", "pendingEmail" => "Direct Debit Payment Pending");
}
function authorizeecheck_nolocalcc()
{
}
function authorizeecheck_localbankdetails()
{
}
function authorizeecheck_capture($params)
{
    $gateway_url = "https://secure2.authorize.net/gateway/transact.dll";
    if ($params["testMode"] == "on") {
        $gateway_url = "https://test.authorize.net/gateway/transact.dll";
    }
    $postfields = array();
    $postfields["x_login"] = $params["loginid"];
    $postfields["x_tran_key"] = $params["transkey"];
    $postfields["x_version"] = "3.1";
    $postfields["x_type"] = "AUTH_CAPTURE";
    $postfields["x_echeck_type"] = "WEB";
    $postfields["x_Method"] = "ECHECK";
    $postfields["x_bank_acct_name"] = $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"];
    $postfields["x_bank_acct_type"] = strtoupper($params["banktype"]);
    $postfields["x_bank_name"] = $params["bankname"];
    $postfields["x_bank_aba_code"] = $params["bankcode"];
    $postfields["x_bank_acct_num"] = $params["bankacct"];
    $postfields["x_relay_response"] = "FALSE";
    $postfields["x_delim_data"] = "TRUE";
    $postfields["x_delim_char"] = "|";
    $postfields["x_encap_char"] = "";
    $postfields["x_invoice_num"] = $params["invoiceid"];
    $postfields["x_first_name"] = $params["clientdetails"]["firstname"];
    $postfields["x_last_name"] = $params["clientdetails"]["lastname"];
    $postfields["x_address"] = $params["clientdetails"]["address1"];
    $postfields["x_city"] = $params["clientdetails"]["city"];
    $postfields["x_state"] = $params["clientdetails"]["state"];
    $postfields["x_zip"] = $params["clientdetails"]["postcode"];
    $postfields["x_country"] = $params["clientdetails"]["country"];
    $postfields["x_phone"] = $params["clientdetails"]["phonenumber"];
    $postfields["x_email"] = $params["clientdetails"]["email"];
    $postfields["x_email_customer"] = "FALSE";
    $postfields["x_amount"] = $params["amount"];
    $postfields["x_solution_id"] = "AAA172607";
    if ($params["testMode"] == "on") {
        $postfields["x_solution_id"] = "AAA100302";
    }
    $resultsarray = array();
    try {
        $data = curlCall($gateway_url, $postfields);
        $temp_values = explode("|", $data);
        $temp_keys = array("Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text", "Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description", "Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name", "Cardholder Last Name", "Company", "Billing Address", "City", "State", "Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name", "Ship to Company", "Ship to Address", "Ship to City", "Ship to State", "Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount", "Tax Exempt Flag", "PO Number", "MD5 Hash", "Card Code (CVV2/CVC2/CID) Response Code", "Cardholder Authentication Verification Value (CAVV) Response Code");
        for ($i = 0; $i <= 27; $i++) {
            array_push($temp_keys, "Reserved Field " . $i);
        }
        for ($i = 0; sizeof($temp_keys) < sizeof($temp_values); $i++) {
            array_push($temp_keys, "Merchant Defined Field " . $i);
        }
        for ($i = 0; $i < sizeof($temp_values); $i++) {
            $resultsarray[(string) $temp_keys[$i]] = $temp_values[$i];
        }
    } catch (Exception $e) {
        $resultsarray["Response Reason Text"] = $e->getMessage();
        $resultsarray["Response Code"] = "3";
    }
    if ($resultsarray["Response Code"] == 1) {
        return array("status" => "success", "transid" => $resultsarray["Transaction ID"], "rawdata" => $resultsarray);
    }
    return array("status" => "declined", "rawdata" => $resultsarray, "declinereason" => $resultsarray["Response Reason Text"]);
}

?>