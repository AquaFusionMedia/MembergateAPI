<?php

    require_once("mgapibase.php");

    class mgapi extends mgapibase {

        var $publicKey;
        var $secretKey;
        var $apiEndpoint;
        public $algorithm = "HMACSHA256";
        public $component = "api";

        function __construct($publicKey,$secretKey,$apiEndpoint) {
            $this->publicKey = $publicKey;
            $this->secretKey = $secretKey;
            $this->apiEndpoint = $apiEndpoint;
        }

        function validateAPI() {
            $qString = array();
            $postData = array();
            return $this->makeRequest("GET",$this->component,"ValidateAPI",$qString,$postData);
        }

    }

?>