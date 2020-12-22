<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$values = array("inputAccountType" => "", "inputBankName" => "", "inputBankAcctHolderName" => "", "inputRoutingNumber" => "", "inputAccountNumber" => "");
$readOnly = "";
if (isset($payMethod)) {
    $payment = $payMethod->payment;
    $values["inputBankName"] = $payment->getBankName();
    $values["inputBankAcctHolderName"] = $payment->getAccountHolderName();
    $values["inputRoutingNumber"] = $payment->getRoutingNumber();
    $values["inputAccountNumber"] = $payment->getAccountNumber();
    foreach ($values as $key => $value) {
        $values[$key] = "value=\"" . $value . "\"";
    }
    $values["inputAccountType"] = $payment->getAccountType();
}
echo "\n<div class=\"payMethodTypeForm typeBankAccount row\">\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputAccountType\">\n            ";
echo AdminLang::trans("payments.accountType");
echo "        </label>\n        <select class=\"form-control\" name=\"bankaccttype\">\n            <option value=\"Checking\"";
echo $values["inputAccountType"] === "Checking" ? " selected" : "";
echo ">\n                ";
echo AdminLang::trans("payments.accountTypeChecking");
echo "            </option>\n            <option value=\"Savings\"";
echo $values["inputAccountType"] === "Savings" ? " selected" : "";
echo ">\n                ";
echo AdminLang::trans("payments.accountTypeSavings");
echo "            </option>\n        </select>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputBankAcctHolderName\">\n            ";
echo AdminLang::trans("payments.accountHolderName");
echo "        </label>\n        <input type=\"text\"\n               id=\"inputBankAcctHolderName\"\n               name=\"bankacctholdername\"\n               class=\"form-control\"\n            ";
echo $values["inputBankAcctHolderName"];
echo "        >\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputBankName\">\n            ";
echo AdminLang::trans("payments.bankName");
echo "        </label>\n        <input type=\"text\"\n               id=\"inputBankName\"\n               name=\"bankname\"\n               class=\"form-control\"\n            ";
echo $values["inputBankName"];
echo "        >\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputRoutingNumber\">\n            ";
echo AdminLang::trans("payments.sortCode");
echo "        </label>\n        <input type=\"text\"\n               id=\"inputRoutingNumber\"\n               name=\"bankroutingnum\"\n               data-enforce-format=\"number\"\n               class=\"form-control\"\n            ";
echo $values["inputRoutingNumber"];
echo "        >\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputAccountNumber\">\n            ";
echo AdminLang::trans("payments.accountNumber");
echo "        </label>\n        <input type=\"text\"\n               id=\"inputAccountNumber\"\n               name=\"bankacctnum\"\n               data-enforce-format=\"number\"\n               class=\"form-control\"\n            ";
echo $values["inputAccountNumber"];
echo "        >\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n</div>\n\n";
echo WHMCS\View\Asset::jsInclude("jquery.payment.js");
echo "\n<script>\n(function (\$) {\n    \$(document).ready(function () {\n        \$('input[data-enforce-format=\"number\"]').payment('restrictNumeric');\n\n        var ccForm = \$('#frmCreditCardDetails');\n\n        if (ccForm.find('#inputAccountNumber').length) {\n            \$.fn.showInputError = function () {\n                this.parents('.form-group').addClass('has-error').find('.field-error-msg').show();\n                return this;\n            };\n\n            window.bankAccountValidate = function () {\n                ccForm.find('.form-group').removeClass('has-error');\n                ccForm.find('.field-error-msg').hide();\n\n                var requiredFields = [\n                    ccForm.find('#inputBankAcctHolderName'),\n                    ccForm.find('#inputBankName'),\n                    ccForm.find('#inputRoutingNumber'),\n                    ccForm.find('#inputAccountNumber')\n                ];\n\n                var complete = true;\n\n                requiredFields.forEach(function(field) {\n                    if (!field.val()) {\n                        field.showInputError();\n                        complete = false;\n                    }\n                });\n\n                return complete;\n            };\n        }\n\n        addAjaxModalSubmitEvents('bankAccountValidate');\n    });\n})(jQuery);\n</script>\n";

?>