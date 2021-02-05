<?php

namespace Mandrill;

require_once 'Mandrill/Exceptions.php';

class Manager {
    
    public $apikey;
    public $ch;
    public $root = 'https://mandrillapp.com/api/1.0';
    public $debug = false;

    public static $error_map = array(
        "ValidationError" => "\Mandrill\ValidationError",
        "Invalid_Key" => "\Mandrill\Invalid_Key",
        "PaymentRequired" => "\Mandrill\PaymentRequired",
        "Unknown_Subaccount" => "\Mandrill\Unknown_Subaccount",
        "Unknown_Template" => "\Mandrill\Unknown_Template",
        "ServiceUnavailable" => "\Mandrill\ServiceUnavailable",
        "Unknown_Message" => "\Mandrill\Unknown_Message",
        "Invalid_Tag_Name" => "\Mandrill\Invalid_Tag_Name",
        "Invalid_Reject" => "\Mandrill\Invalid_Reject",
        "Unknown_Sender" => "\Mandrill\Unknown_Sender",
        "Unknown_Url" => "\Mandrill\Unknown_Url",
        "Unknown_TrackingDomain" => "\Mandrill\Unknown_TrackingDomain",
        "Invalid_Template" => "\Mandrill\Invalid_Template",
        "Unknown_Webhook" => "\Mandrill\Unknown_Webhook",
        "Unknown_InboundDomain" => "\Mandrill\Unknown_InboundDomain",
        "Unknown_InboundRoute" => "\Mandrill\Unknown_InboundRoute",
        "Unknown_Export" => "\Mandrill\Unknown_Export",
        "IP_ProvisionLimit" => "\Mandrill\IP_ProvisionLimit",
        "Unknown_Pool" => "\Mandrill\Unknown_Pool",
        "NoSendingHistory" => "\Mandrill\NoSendingHistory",
        "PoorReputation" => "\Mandrill\PoorReputation",
        "Unknown_IP" => "\Mandrill\Unknown_IP",
        "Invalid_EmptyDefaultPool" => "\Mandrill\Invalid_EmptyDefaultPool",
        "Invalid_DeleteDefaultPool" => "\Mandrill\Invalid_DeleteDefaultPool",
        "Invalid_DeleteNonEmptyPool" => "\Mandrill\Invalid_DeleteNonEmptyPool",
        "Invalid_CustomDNS" => "\Mandrill\Invalid_CustomDNS",
        "Invalid_CustomDNSPending" => "\Mandrill\Invalid_CustomDNSPending",
        "Metadata_FieldLimit" => "\Mandrill\Metadata_FieldLimit",
        "Unknown_MetadataField" => "\Mandrill\Unknown_MetadataField"
    );

    public function __construct($apikey=null) {
        if(!$apikey) $apikey = getenv('\Mandrill\APIKEY');
        if(!$apikey) $apikey = $this->readConfigs();
        if(!$apikey) throw new \Mandrill\Error('You must provide a Mandrill API key');
        $this->apikey = $apikey;

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mandrill-PHP/1.0.55');
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);

        $this->root = rtrim($this->root, '/') . '/';

        $this->templates = new \Mandrill\Templates($this);
        $this->exports = new \Mandrill\Exports($this);
        $this->users = new \Mandrill\Users($this);
        $this->rejects = new \Mandrill\Rejects($this);
        $this->inbound = new \Mandrill\Inbound($this);
        $this->tags = new \Mandrill\Tags($this);
        $this->messages = new \Mandrill\Messages($this);
        $this->whitelists = new \Mandrill\Whitelists($this);
        $this->ips = new \Mandrill\Ips($this);
        $this->internal = new \Mandrill\Internal($this);
        $this->subaccounts = new \Mandrill\Subaccounts($this);
        $this->urls = new \Mandrill\Urls($this);
        $this->webhooks = new \Mandrill\Webhooks($this);
        $this->senders = new \Mandrill\Senders($this);
        $this->metadata = new \Mandrill\Metadata($this);
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function call($url, $params) {
        $params['key'] = $this->apikey;
        $params = json_encode($params);
        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $this->root . $url . '.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

        $start = microtime(true);
        $this->log('Call to ' . $this->root . $url . '.json: ' . $params);
        if($this->debug) {
            $curl_buffer = fopen('php://memory', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $curl_buffer);
        }

        $response_body = curl_exec($ch);
        $info = curl_getinfo($ch);
        $time = microtime(true) - $start;
        if($this->debug) {
            rewind($curl_buffer);
            $this->log(stream_get_contents($curl_buffer));
            fclose($curl_buffer);
        }
        $this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
        $this->log('Got response: ' . $response_body);

        if(curl_error($ch)) {
            throw new \Mandrill\HttpError("API call to $url failed: " . curl_error($ch));
        }
        $result = json_decode($response_body, true);
        if($result === null) throw new \Mandrill\Error('We were unable to decode the JSON response from the Mandrill API: ' . $response_body);
        
        if(floor($info['http_code'] / 100) >= 4) {
            throw $this->castError($result);
        }

        return $result;
    }

    public function readConfigs() {
        $paths = array('~/.mandrill.key', '/etc/mandrill.key');
        foreach($paths as $path) {
            if(file_exists($path)) {
                $apikey = trim(file_get_contents($path));
                if($apikey) return $apikey;
            }
        }
        return false;
    }

    public function castError($result) {
        if($result['status'] !== 'error' || !$result['name']) throw new \Mandrill\Error('We received an unexpected error: ' . json_encode($result));

        $class = (isset(self::$error_map[$result['name']])) ? self::$error_map[$result['name']] : '\Mandrill\Error';
        return new $class($result['message'], $result['code']);
    }

    public function log($msg) {
        if($this->debug) error_log($msg);
    }
}


