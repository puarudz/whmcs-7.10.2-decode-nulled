<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Notification\Slack;

class Slack implements \WHMCS\Module\Contracts\NotificationModuleInterface
{
    use \WHMCS\Module\Notification\DescriptionTrait;
    const API_URL = "https://slack.com/api/";
    public function __construct()
    {
        $this->setDisplayName("Slack")->setLogoFileName("logo.png");
    }
    public function settings()
    {
        $helpText = \AdminLang::trans("help.contextlink");
        $helpLink = "<div class=\"pull-right\">\n    <a href=\"http://docs.whmcs.com/Configuring_Notifications_with_Slack\"\n       class=\"btn btn-default btn-xs\"\n       target=\"_blank\"\n    >\n        <i class=\"fal fa-lightbulb\"></i>\n        " . $helpText . "\n    </a>\n</div>";
        return array("oauth_token" => array("FriendlyName" => "OAuth Access Token" . $helpLink, "Type" => "text", "Description" => "An OAuth token for the Custom App you have installed" . " in your Slack workspace." . " Your App needs the \"channels:read\", \"channels:join\"" . " and \"chat:write\" scopes." . " If you wish to notify private channels," . " the scope \"groups:read\" is also required."));
    }
    public function testConnection($settings)
    {
        $uri = "conversations.list";
        $postdata = array("limit" => "1");
        try {
            $this->call($settings, $uri, $postdata);
        } catch (\WHMCS\Exception $e) {
            $errorMsg = $e->getMessage();
            if ($errorMsg == "An error occurred: invalid_auth") {
                $errorMsg = "Token is invalid. Please check your input and try again.";
            }
            throw new \WHMCS\Exception($errorMsg);
        }
    }
    public function notificationSettings()
    {
        return array("channel" => array("FriendlyName" => "Channel", "Type" => "dynamic", "Description" => "Select the desired channel for a notification.<br>" . "Private Channels are shown with *", "Required" => true), "message" => array("FriendlyName" => "Customise Message", "Type" => "text", "Description" => "Allows you to customise the primary display message shown in the notification."));
    }
    public function getDynamicField($fieldName, $settings)
    {
        if ($fieldName == "channel") {
            $uri = "conversations.list";
            $postdata = array("types" => "public_channel,private_channel", "limit" => "2000", "exclude_members" => true);
            $response = $this->call($settings, $uri, $postdata);
            $channels = array();
            foreach ($response->channels as $channel) {
                $channelName = $channel->name;
                if ($channel->is_group && $channel->is_private) {
                    $channelName .= "*";
                }
                $channels[] = array("id" => $channel->id, "name" => $channelName);
            }
            usort($channels, function ($a, $b) {
                return strnatcmp($a["name"], $b["name"]);
            });
            return array("values" => $channels);
        } else {
            return array();
        }
    }
    public function sendNotification(\WHMCS\Notification\Contracts\NotificationInterface $notification, $moduleSettings, $notificationSettings)
    {
        $messageBody = $notification->getMessage();
        if ($notificationSettings["message"]) {
            $messageBody = $notificationSettings["message"];
        }
        $attachment = (new Attachment())->fallback($messageBody . " " . $notification->getUrl())->title(\WHMCS\Input\Sanitize::decode($notification->getTitle()))->title_link($notification->getUrl())->text($messageBody);
        foreach ($notification->getAttributes() as $attribute) {
            $value = $attribute->getValue();
            if ($attribute->getUrl()) {
                $value = "<" . $attribute->getUrl() . "|" . $value . ">";
            }
            $attachment->addField((new Field())->title($attribute->getLabel())->value($value)->short());
        }
        $channel = $notificationSettings["channel"];
        $channel = explode("|", $channel, 2);
        $channelId = $channel[0];
        $message = (new Message())->channel($channelId)->username("WHMCS Bot")->attachment($attachment);
        $uri = "chat.postMessage";
        $this->call($moduleSettings, $uri, $message->toArray());
    }
    protected function call(array $settings, $uri, array $postdata = array(), $throwOnError = true)
    {
        $postdata["token"] = $settings["oauth_token"];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL . $uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($response);
        logModuleCall("slack", $uri, $postdata, $response, $decoded, array($settings["oauth_token"]));
        if (!isset($decoded->ok)) {
            throw new \WHMCS\Exception("Bad response: " . $response);
        }
        if ($decoded->ok == false && $throwOnError) {
            throw new \WHMCS\Exception("An error occurred: " . $decoded->error);
        }
        return $decoded;
    }
    public function postRuleSave(array $moduleConfiguration, array $providerConfig)
    {
        $channel = $providerConfig["channel"];
        $channel = explode("|", $channel, 2);
        $channelId = $channel[0];
        $uri = "conversations.join";
        $postData = array("channel" => $channelId);
        $response = $this->call($moduleConfiguration, $uri, $postData, false);
        if ($response->ok === false) {
            switch ($response->error) {
                case "missing_scope":
                case "no_permission":
                    throw new \WHMCS\Exception\Information("Missing Scope: Your App needs the \"channels:join\" scope");
                default:
                    throw new \WHMCS\Exception("An error occurred: " . $response->error);
            }
        }
    }
}

?>