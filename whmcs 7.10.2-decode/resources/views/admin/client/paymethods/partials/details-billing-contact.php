<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$newContactHref = "clientscontacts.php?userid=" . $client->id . "&contactid=addnew";
$viewPartial = new WHMCS\Admin\ApplicationSupport\View\Html\Helper\ContactSelectedDropDown($client, true, "billingContactId", "client");
$selectOptions = array();
$selectOptions[] = array("id" => "client", "type" => "client", "descriptor" => "", "name" => $client->fullName, "companyname" => $client->companyName, "email" => $client->email, "address" => $client->address1 . " " . $client->state . " " . $client->postcode . " " . $client->countryName);
foreach ($client->contacts as $contact) {
    $selectOptions[] = array("id" => $contact->id, "type" => "contact", "descriptor" => "", "name" => $contact->fullName, "companyname" => $contact->companyName, "email" => $contact->email, "address" => $contact->address1 . " " . $contact->state . " " . $contact->postcode . " " . $contact->countryName);
}
$selectedId = $client->billingContactId ? $client->billingContactId : "client";
if (isset($payMethod)) {
    $contact = $payMethod->contact;
    if (!$contact instanceof WHMCS\User\Client) {
        $selectedId = $contact->id;
    }
}
$selected = "payMethodSelectized.setValue('" . $selectedId . "', '');";
echo "<script>\n    jQuery(document).ready(function() {\n        var selectContact = jQuery('#selectBillingContact'),\n            payMethodSelectized = WHMCS.selectize.billingContacts(\n            '#selectBillingContact',\n            ";
echo json_encode($selectOptions);
echo ",\n            {\n                optgroupField: 'type',\n                optgroupLabelField: 'name',\n                optgroupValueField: 'id',\n                optgroups: [\n                    {\$order: 1, id: 'client', name: selectContact.data('client-label')},\n                    {\$order: 2, id: 'contact', name: selectContact.data('contact-label')}\n                ],\n            }\n        );\n        ";
echo $selected;
echo "    });\n</script>\n<div class=\"row\">\n    <div class=\"col-sm-12\">\n        <div class=\"form-group\" style=\"min-height: 9em\">\n            <label for=\"inputDescription\">\n                ";
echo AdminLang::trans("payments.billingAddress");
echo " (<a class=\"link\" target=\"_blank\" href=\"";
echo $newContactHref;
echo "\">";
echo AdminLang::trans("global.manage");
echo "</a>)\n            </label>\n            <select id=\"selectBillingContact\"\n                name=\"billingContactId\"\n                class=\"form-control selectize\"\n                data-value-field=\"id\"\n                data-client-label=\"";
echo AdminLang::trans("fields.client");
echo "\"\n                data-contact-label=\"";
echo AdminLang::trans("fields.contacts");
echo "\"\n                data-search-url=\"";
echo routePath("admin-search-client-contacts", $client->id);
echo "\"\n                placeholder=\"";
echo AdminLang::trans("global.typeToSearchContacts");
echo "\">\n            </select>\n        </div>\n    </div>\n</div>\n";

?>