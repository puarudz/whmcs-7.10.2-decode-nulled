<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Updater\Version;

class Version790rc1 extends IncrementalVersion
{
    protected $updateActions = array("ensureOfflineCCTypeIsReplaced", "updateCreditCardPaymentConfirmation");
    protected function ensureOfflineCCTypeIsReplaced()
    {
        \WHMCS\Database\Capsule::table("tblpaymentgateways")->where("setting", "type")->where("value", "OfflineCC")->update(array("value" => \WHMCS\Module\Gateway::GATEWAY_CREDIT_CARD));
        return $this;
    }
    protected function updateCreditCardPaymentConfirmation()
    {
        $md5Values = array("68c28a219187cd1131c06dbc35f491d9");
        $newMessage = "<p>Dear {\$client_name},</p>\n<p>This is a payment receipt for Invoice {\$invoice_num} sent on {\$invoice_date_created}</p>\n<p>{\$invoice_html_contents}</p>\n<p>\nAmount: {\$invoice_last_payment_amount}<br />\nPay Method: {\$invoice_payment_method} ({\$invoice_pay_method_display_name})<br />\nTransaction #: {\$invoice_last_payment_transid}<br />\nTotal Paid: {\$invoice_amount_paid}<br />\nRemaining Balance: {\$invoice_balance}<br />\nStatus: {\$invoice_status}\n</p>\n<p>You may review your invoice history at any time by logging in to your client area.</p>\n<p>Note: This email will serve as an official receipt for this payment.</p>\n<p>{\$signature}</p>";
        $template = \WHMCS\Mail\Template::master()->where("name", "Credit Card Payment Confirmation")->first();
        if ($template && in_array(md5($template->message), $md5Values)) {
            $template->message = $newMessage;
            $template->save();
        }
        if (!$template) {
            $template = new \WHMCS\Mail\Template();
            $template->name = "Credit Card Payment Confirmation";
            $template->subject = "Credit Card Payment Confirmation";
            $template->message = $newMessage;
            $template->custom = false;
            $template->attachments = array();
            $template->type = "product";
            $template->plaintext = false;
            $template->fromEmail = "";
            $template->fromName = "";
            $template->language = "";
            $template->save();
        }
        return $this;
    }
}

?>