<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\Paypalcheckout;

class ApiClient
{
    protected $useSandbox = false;
    protected $options = array();
    protected $accessToken = NULL;
    protected $sendPartnerId = false;
    protected $response = NULL;
    protected $httpResponseCode = NULL;
    const SANDBOX_URL = "https://api.sandbox.paypal.com/";
    const LIVE_URL = "https://api.paypal.com/";
    const PARTNER_ATTRIBUTION_ID = "WHMCS_Ecom_PPCP";
    public function setSandbox($enabled)
    {
        $this->useSandbox = (bool) $enabled;
        return $this;
    }
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }
    public function setSendPartnerId($send)
    {
        $this->sendPartnerId = (bool) $send;
        return $this;
    }
    protected function getBaseUrl()
    {
        if ($this->useSandbox) {
            return self::SANDBOX_URL;
        }
        return self::LIVE_URL;
    }
    protected function getOptions()
    {
        if (empty($this->options)) {
            return array("HEADER" => array("Content-Type: application/json", "Authorization: Bearer " . $this->accessToken));
        }
        return $this->options;
    }
    public function get($endpoint)
    {
        return $this->call("GET", $endpoint);
    }
    public function post($endpoint, $data = NULL)
    {
        return $this->call("POST", $endpoint, $data);
    }
    protected function call($method, $endpoint, $data = NULL)
    {
        $options = $this->getOptions();
        if ($method == "POST") {
            $options["CURLOPT_POST"] = true;
        }
        if ($this->sendPartnerId) {
            $options["HEADER"][] = "PayPal-Partner-Attribution-Id: " . self::PARTNER_ATTRIBUTION_ID;
        }
        $ch = curlCall($this->getBaseUrl() . $endpoint, $data, $options, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->setResponse($response, $httpCode);
        logModuleCall("PayPal", $endpoint . ($this->useSandbox ? " [SANDBOX]" : ""), $data, "HTTP Response Code: " . $httpCode . PHP_EOL . $response, $this->decodedResponse);
        if (curl_errno($ch)) {
            throw new \WHMCS\Exception\Http\ConnectionError(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        if ($this->isAuthError()) {
            throw new Exception\AuthError();
        }
        return $this;
    }
    public function setResponse($response, $httpCode)
    {
        $this->httpResponseCode = $httpCode;
        $this->response = $response;
        $this->decodedResponse = json_decode($response);
    }
    public function isError()
    {
        return $this->httpResponseCode < 200 || 300 <= $this->httpResponseCode;
    }
    public function getResponse()
    {
        return $this->decodedResponse;
    }
    public function getFromResponse($key)
    {
        return isset($this->decodedResponse->{$key}) ? $this->decodedResponse->{$key} : null;
    }
    public function getError()
    {
        return $this->getFromResponse("error");
    }
    public function isAuthError()
    {
        return $this->httpResponseCode == 401;
    }
}

?>