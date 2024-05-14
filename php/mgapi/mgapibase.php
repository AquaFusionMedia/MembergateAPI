<?php

    class mgapibase {

        /* Request Modules */

        function makeRequest($method,$component,$apiMethod,$qString,$payload) {
            $queryString = '';
            if (is_array($qString)) {
                $queryString = $this->buildQueryString($qString);
            } else if (is_string($qString)) {
                $queryString = $qString;
            }
            if ($method == "GET") {
                $payload = "";
            }
            $rTimestamp = $this->mgDateFormatter();
            $rSignature = $this->buildSignature($method,$component,$apiMethod,$qString,$payload,$rTimestamp);
            /* Make the Request */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint."?".$queryString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            $headers = array();
            $headers[] = "publickey: ".$this->publicKey;
            $headers[] = "timestamp: ".$rTimestamp["dateTimeFormatted"];
            $headers[] = "component: ".$component;
            $headers[] = "method: ".$apiMethod;
            $headers[] = "signature: ".$rSignature["signature"];
            if (is_array($payload)) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildQueryString($payload));
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MGAPI');
            curl_setopt($ch, CURLOPT_HEADERFUNCTION,
                function($curl, $header) use (&$apiResponse) {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) // ignore invalid headers
                    return $len;

                    $apiResponse[strtolower(trim($header[0]))][] = trim($header[1]);
                    
                    return $len;
                }
            );
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close ($ch);
            return $this->parseResponse($apiResponse);  
        }

        function parseResponse($apiResponse) {
            $apiStruct = array();
            if (isset($apiResponse['apiresponse'])) {
                $apiJSON = json_decode($apiResponse['apiresponse'][0],true);
                if (isset($apiJSON['statuscode'])) {
                    switch (substr($apiJSON['statuscode'],0,1)) {
                        case '2':
                            $apiStruct['error'] = false;
                            $apiStruct['statuscode'] = $apiJSON['statuscode'];
                            if (isset($apiJSON['message'])) {
                                $apiStruct['message'] = $apiJSON['message'];
                            } else {
                                $apiStruct['message'] = 'API Success';
                            }
                            if (isset($apiJSON['data'])) {
                                $apiStruct['data'] = json_decode($apiJSON['data'],true);;
                            }
                            break;
                        default:
                            $apiStruct['error'] = true;
                            $apiStruct['statuscode'] = $apiJSON['statuscode'];
                            if (isset($apiJSON['message'])) {
                                $apiStruct['message'] = $apiJSON['message'];
                            } else {
                                $apiStruct['message'] = 'API Error';
                            }
                    }
                } else {
                    $apiStruct['error'] = true;
                    $apiStruct['message'] = 'API Error';
                }
            } else {
                $apiStruct['error'] = true;
                $apiStruct['message'] = 'API Error';
            }
            return $apiStruct;
        }

        /* Signature Modules */

        function buildSignature($method,$component,$apiMethod,$qString,$payload,$rTimestamp) {
            $bSig = array(
                "signingKey" => $this->buildSigningKey($method,$component,$apiMethod,$qString,$payload,$rTimestamp),
                "stringToSign" => $this->buildStringToSign($method,$component,$apiMethod,$qString,$payload,$rTimestamp)
            );
            $bSig['signature'] = strtoupper(hash_hmac("sha256",$bSig["stringToSign"],$bSig["signingKey"]));
            return $bSig;
        }

        function buildSigningKey($method,$component,$apiMethod,$qString,$payload,$rTimestamp) {
            $kSecret = "MGAPI".$this->secretKey;
            $kDate = strtoupper(hash_hmac("sha256",$rTimestamp["dateTimeFormatted"],$kSecret));
            $kMethod = strtoupper(hash_hmac("sha256",$method,$kDate));
            $kComponent = strtoupper(hash_hmac("sha256",$component,$kMethod));
            $kSigning = strtoupper(hash_hmac("sha256",$apiMethod,$kComponent));
            return $kSigning;
        }

        function buildStringToSign($method,$component,$apiMethod,$qString,$payload,$rTimestamp) {
            /* Start with the Algorithm */
            $stringToSign = $this->algorithm;
            /* Add the Timestamp */
            $stringToSign = $stringToSign."\n".$rTimestamp["dateTimeFormatted"];
            /* Add the Method */
            $stringToSign = $stringToSign."\n".$method;
            /* Add the Component */
            $stringToSign = $stringToSign."\n".$component;
            /* Add the API Method */
            $stringToSign = $stringToSign."\n".$apiMethod;
            /* Add the Hash of the qString */
            $stringToSign = $stringToSign."\n".strtoupper(hash("sha256",$this->parseRequestData($qString)));
            /* Add the Hash of the Request Body */
            $stringToSign = $stringToSign."\n".strtoupper(hash("sha256",$this->parseRequestData($payload)));
            return $stringToSign;
        }

        /* Worker Modules */

        function mgDateFormatter($mgDate = NULL) {
            if (!(bool)$mgDate) {
                $dateStruct = array(
                    "dateTime" => gmdate("Ymd")."T".gmdate("His")."Z",
                    "dateTimeFormatted" => gmdate("Y-m-d")."T".gmdate("H:i:s"),
                    "date" => gmdate("Ymd")
                );
            } else {
                $dateStruct = array(
                    "dateTime" => date_format($mgDate,"Ymd")."T".date_format($mgDate,"His")."Z",
                    "dateTimeFormatted" => date_format($mgDate,"Y-m-d")."T".date_format($mgDate,"H:i:s"),
                    "date" => date_format($mgDate,"Ymd")
                );
            }
            return $dateStruct;
        }

        function parseRequestData($requestData) {
            $requestStruct = array();
            /* Array Request Data */
            if (is_array($requestData)) {
                foreach ($requestData as $key => $val) {
                    $requestStruct[$key] = (string)$val;
                }
            /* NVP Request Data */
            } else if (is_string($requestData) && strlen($requestData)) {
                $pairs = explode('&',$requestData);
                foreach ($pairs as $key => $val) {
                    if (strlen($key)) {
                        $requestStruct[$key] = (string)$val;
                    }
                }
            }
            if (count($requestStruct)) {
                ksort($requestStruct);
                return json_encode($requestStruct);
            } else {
                return '{}';
            }
        }

        function buildQueryString($qString) {
            ksort($qString);
            return http_build_query($qString);
        }

        /* Base Modules */

        function publicKey() {
            return $this->publicKey;
        }

        function secretKey() {
            return $this->secretKey;
        }

        function apiEndpoint() {
            return $this->apiEndpoint;
        }

        function algorithm() {
            return $this->algorithm;
        }

    }

?>