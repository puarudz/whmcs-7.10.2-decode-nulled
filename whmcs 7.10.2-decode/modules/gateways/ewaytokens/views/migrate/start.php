<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<form action=\"";
echo $routePath;
echo "\" id=\"frmEWAYMigrate\">\n    <input type=\"hidden\" name=\"action\" value=\"migrate\">\n    ";
echo generate_token();
echo "    <div>\n        WHMCS recommends using the eWAY module in place of the eWAY Rapid Payments module.\n        <br>\n        This process will aid in migrating any existing client payment methods to the new module.\n        <br>\n        This ensures a seamless process for your clients and no interruption in payment processing.\n        <br>\n        <br>\n        The following actions will be completed on submitting this form:\n    </div>\n    <ol>\n        ";
if (!$eWayActive) {
    echo "            <li>\n                The eWAY Module will be activated\n            </li>\n        ";
}
if ($apiKeyRequired) {
    echo "            <li>\n                The eWAY Public API Key will be set.<br>\n                Please enter your eWAY Public API Key:<br>\n                <div class=\"form-group\">\n                    <input class=\"form-control input-400\" name=\"eway_public_key\" id=\"ewayKey\">\n                    <span class=\"field-error-msg\">\n                        The public API key is required for the updated eWAY module\n                    </span>\n                </div>\n            </li>\n        ";
}
if (0 < $payMethodsToMigrate) {
    echo "            <li>\n                All Client Payment Methods for eWAY Tokens will be migrated to the new module\n            </li>\n        ";
}
echo "        <li>\n            The eWAY Rapid Module will be deactivated and replaced with eWAY\n        </li>\n    </ol>\n</form>\n";
if ($apiKeyRequired) {
    echo "<script>\n    (function (\$) {\n        \$(document).ready(function () {\n\n            var frm = \$('#frmEWAYMigrate');\n\n            \$.fn.showInputError = function () {\n                this.parents('.form-group').addClass('has-error')\n                    .find('.field-error-msg')\n                    .show();\n                return this;\n            };\n\n            window.validateRequiredFields = function () {\n                frm.find('.form-group').removeClass('has-error');\n                frm.find('.field-error-msg').hide();\n\n                var requiredFields = [\n                    frm.find('#ewayKey'),\n                ];\n\n                var complete = true;\n\n                requiredFields.forEach(function(field) {\n                    if (!field.val()) {\n                        field.showInputError();\n                        complete = false;\n                    }\n                });\n\n                return complete;\n            };\n\n            addAjaxModalSubmitEvents('validateRequiredFields');\n        });\n    })(jQuery);\n</script>\n";
}

?>