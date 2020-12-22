<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (empty($payMethodType)) {
    $type = strtolower($payMethod->getType());
} else {
    $type = strtolower($payMethodType);
}
echo "\n<div class=\"row\">\n    <div class=\"col-md-12 text-center bottom-margin-5\">\n        ";
echo AdminLang::trans("clients.ccdeletesure");
echo "    </div>\n    <div class=\"col-sm-12 col-md-12 text-center\">\n        <form id=\"frmCreditCardDeleteDetails\" class=\"form\" method=\"POST\" action=\"";
echo $deleteUrl;
echo "\">\n            <input type=\"hidden\" name=\"payMethodId\" value=\"";
echo $payMethod->id;
echo "\"/>\n            <input type=\"hidden\" name=\"payMethodType\" value=\"";
echo $payMethod->getType();
echo "\" />\n            <input type=\"hidden\" name=\"billingContactId\" value=\"client\" />\n\n            <div id=\"remoteFailureDetails\" class=\"bottom-margin-10\" style=\"display: none\">\n                <div class=\"alert alert-danger\">\n                </div>\n\n                <label for=\"ignoreRemoteFailure\">\n                    <input type=\"checkbox\" id=\"ignoreRemoteFailure\" name=\"ignoreRemoteFailure\" value=\"1\" />\n                    ";
echo AdminLang::trans("payments.ignoreErrorAndDelete");
echo "                </label>\n            </div>\n\n            <button title=\"submit\" class=\"btn btn-danger\" data-role=\"btn-confirm-paymethod-deletion\">\n                ";
echo AdminLang::trans("global.delete");
echo "            </button>\n        </form>\n    </div>\n</div>\n";

?>