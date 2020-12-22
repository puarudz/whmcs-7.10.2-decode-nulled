<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (empty($payMethodType) && $payMethod) {
    $payMethodType = $payMethod->getType();
}
$type = strtolower($payMethodType);
$hasDetails = $type !== "remotebankaccount";
$inactiveGateway = "";
if (isset($payMethod) && $payMethod->isUsingInactiveGateway()) {
    $inactiveGateway = AdminLang::trans("clientsummary.inactiveGatewayRemoteToken");
}
if ($inactiveGateway) {
    echo "<div class=\"alert alert-info\">" . $inactiveGateway . "</div>";
}
echo "<div class=\"alert alert-danger admin-modal-error\" style=\"display: none\">\n</div>\n";
if ($remoteInput) {
    echo "    <div id=\"divRemoteInput\">\n        ";
    echo $remoteInput;
    echo "    </div>\n    <iframe name=\"ccframe\" class=\"auth3d-area\" width=\"100%\" height=\"400\" scrolling=\"auto\" src=\"http://about:blank\"></iframe>\n    <script>\n        jQuery(\"#divRemoteInput\").find(\"form:first\").attr('target', 'ccframe');\n        setTimeout(\"autoSubmitFormByContainer('divRemoteInput')\", 1000);\n        jQuery('#btnSave').hide();\n        jQuery('#modalAjax').off('hide.bs.modal').on('hide.bs.modal', function() {\n            reloadTablePayMethods();\n        });\n    </script>\n";
} else {
    if ($remoteUpdate) {
        echo "    <div id=\"divRemoteUpdate\">\n        ";
        echo $remoteUpdate;
        echo "    </div>\n    <script>\n        jQuery('#savePaymentMethod').hide();\n    </script>\n";
    } else {
        echo "    <form id=\"frmCreditCardDetails\" class=\"form\" method=\"POST\" action=\"";
        echo $actionUrl;
        echo "\">\n        <input type=\"hidden\" name=\"payMethodId\" value=\"";
        echo $payMethod ? $payMethod->id : "";
        echo "\"/>\n        <input type=\"hidden\" name=\"payMethodType\" value=\"";
        echo $payMethodType;
        echo "\"/>\n        <div class=\"row\">\n            <div class=\"col-sm-12\">\n                <div class=\"alert alert-danger text-center gateway-errors hidden\"></div>\n            </div>\n        </div>\n        <div class=\"row\">\n            ";
        if ($hasDetails) {
            echo "            <div class=\"col-sm-6\">\n                ";
            $this->insert("client/paymethods/partials/details-" . $type);
            echo "            </div>\n            ";
        }
        echo "            <div class=\"col-sm-";
        echo $hasDetails ? "6" : "12";
        echo "\">\n                ";
        $this->insert("client/paymethods/partials/details-paymethod-attributes");
        echo "            </div>\n        </div>\n    </form>\n";
    }
}
echo "<script type=\"text/javascript\">\n    var modal = \$('#modalAjax');\n\n    if (!\$(modal).data('remove-payment-delete-btn')) {\n        \$(modal).data('remove-payment-delete-btn', true);\n\n        \$(modal).on('hide.bs.modal', function () {\n            \$('#divDeleteButton').remove();\n        });\n    };\n\n    ";
if (isset($deleteUrl)) {
    echo "    \$('#divDeleteButton').remove();\n    \$('#modalAjaxLoader').before('<div id=\"divDeleteButton\" class=\"pull-left\"><a class=\"btn btn-danger delete-paymethod open-modal pull-right\" href=\"";
    echo $deleteUrl;
    echo "\" data-role=\"btn-delete-paymethod\">";
    echo AdminLang::trans("global.delete");
    echo "</a></div>');\n    \$('.delete-paymethod').off('click').on('click', function() {\n        \$('#divDeleteButton').remove();\n    });\n    ";
}
if ($inactiveGateway) {
    echo "        \$('#savePaymentMethod').prop('disabled', true)\n            .addClass('disabled');\n    ";
}
echo "</script>\n";

?>