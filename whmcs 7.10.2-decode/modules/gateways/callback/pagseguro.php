<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("pagseguro");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
$PagSeguro = "Comando=validar";
$PagSeguro .= "&Token=" . $GATEWAY["callbacktoken"];
foreach ($_POST as $k => $v) {
    $PagSeguro .= "&" . $k . "=" . urlencode(stripslashes($v));
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://pagseguro.uol.com.br/Security/NPI/Default.aspx");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $PagSeguro);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$resp = curl_exec($ch);
if (!tep_not_null($resp)) {
    curl_setopt($ch, CURLOPT_URL, "https://pagseguro.uol.com.br/Security/NPI/Default.aspx");
    $resp = curl_exec($ch);
}
curl_close($ch);
if (strcmp($resp, "VERIFICADO") == 0) {
    $VendedorEmail = addslashes($_POST["VendedorEmail"]);
    $TransacaoID = addslashes($_POST["TransacaoID"]);
    $Referencia = (int) $_POST["Referencia"];
    $StatusTransacao = addslashes($_POST["StatusTransacao"]);
    $TipoPagamento = addslashes($_POST["TipoPagamento"]);
    $CliNome = addslashes($_POST["CliNome"]);
    $NumItens = addslashes($_POST["NumItens"]);
    $ProdValor = number_format(str_replace(array(",", "."), ".", addslashes($_POST["ProdValor_1"])), 2, ".", "");
    $Taxa = 0;
    $invoiceid = checkCbInvoiceID($Referencia, $GATEWAY["paymentmethod"]);
    switch ($TipoPagamento) {
        case "Boleto":
        case "Pagamento":
        case "Pagamento Online":
            $Taxa = $ProdValor * 2.9 / 100 + 0.4;
            break;
        case "Cartão de Crédito":
            $Taxa = $ProdValor * 6.4 / 100 + 0.4;
            break;
    }
    $payments = WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->first(array("userid", "status"));
    $userid = $payments->userid;
    $status = $payments->status;
    if ($GATEWAY["convertto"]) {
        $currency = getCurrency($userid);
        $ProdValor = convertCurrency($ProdValor, $GATEWAY["convertto"], $currency["id"]);
        $Taxa = convertCurrency($Taxa, $GATEWAY["convertto"], $currency["id"]);
    }
    if ($GATEWAY["email"] != $VendedorEmail) {
        logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Invalid Vendor Email");
    } else {
        if ($StatusTransacao == "Aprovado") {
            if ($status == "Unpaid") {
                addInvoicePayment($invoiceid, $TransacaoID, $ProdValor, $Taxa, "pagseguro");
            }
            logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Incomplete");
            redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
        } else {
            if ($StatusTransacao == "Completo") {
                $status = WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->value("status");
                if ($status == "Unpaid") {
                    addInvoicePayment($invoiceid, $TransacaoID, $ProdValor, $Taxa, "pagseguro");
                }
                logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Completed");
                redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
            } else {
                if ($StatusTransacao == "Cancelado") {
                    logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Cancelled");
                    redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
                } else {
                    logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Processing");
                    redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
                }
            }
        }
    }
} else {
    logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Error");
    redirSystemURL("action=invoices", "clientarea.php");
}
function tep_not_null($value)
{
    if (is_array($value)) {
        if (0 < sizeof($value)) {
            return true;
        }
        return false;
    }
    if ($value != "" && $value != "NULL" && 0 < strlen(trim($value))) {
        return true;
    }
    return false;
}

?>