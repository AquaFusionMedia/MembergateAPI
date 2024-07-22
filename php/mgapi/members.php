<?php

    require_once("mgapibase.php");

    class members extends mgapibase {

        var $publicKey;
        var $secretKey;
        var $apiEndpoint;
        public $algorithm = "HMACSHA256";
        public $component = "member";

        function __construct($publicKey,$secretKey,$apiEndpoint) {
            $this->publicKey = $publicKey;
            $this->secretKey = $secretKey;
            $this->apiEndpoint = $apiEndpoint;
        }

        /* Get Member by Member Number */
        function getMemberByMemberNumber($member_number) {
            $qString = array(
                "member_number" => $member_number
            );
            $postData = array();
            return $this->makeRequest("GET",$this->component,"getMemberByMemberNumber",$qString,$postData);
        }

        /* Get Member by UUID */
        function getMemberByMemberUUID($memberuuid) {
            $qString = array(
                "memberuuid" => $memberuuid
            );
            $postData = array();
            return $this->makeRequest("GET",$this->component,"getMemberByMemberUUID",$qString,$postData);
        }

        /* Add/Edit Member */
        function addEditMember(
                $first_name, $last_name, $email, $account_type, $userid, $password,
                $company=null, $member_job_title=null, $address=null, $address2=null, $city=null, $state=null, $provincee=null, $postal_code=null, $country=null, $work_phone=null, $parentid=null, $renew=null) {
            foreach (get_defined_vars() as $key => $value) {
                if ($value != NULL) {
                    $postData[$key] = $value;
                }
            }
            $postData['passcheck'] = $postData['password'];
            $postData['pay_method'] = "comp";
            $qString = array();
            return $this->makeRequest("POST",$this->component,"addEditMember",$qString,$postData);
        }

        /* Update Member UserID */
        function updateMember(
            $memberuuid,
            $first_name=null, $last_name=null, $email=null, $userid=null, $company=null, $member_job_title=null, $address=null, $address2=null, $city=null, $state=null, $provincee=null, $postal_code=null, $country=null, $work_phone=null) {
            foreach (get_defined_vars() as $key => $value) {
                if ($key != 'memberuuid' && $value != NULL) {
                    $postData[$key] = $value;
                }
            }
            $qString = array(
                "memberuuid" => $memberuuid
            );
            return $this->makeRequest("PATCH",$this->component,"updateMember",$qString,$postData);
        }

        /* Update Member UserID */
        function updateMemberUserID($memberuuid,$userid) {
            $qString = array(
                "memberuuid" => $memberuuid
            );
            $postData = array(
                "userid" => $userid
            );
            return $this->makeRequest("PATCH",$this->component,"updateMemberUserID",$qString,$postData);
        }

        /* Update Member Email */
        function updateMemberEmail($memberuuid,$email) {
            $qString = array(
                "memberuuid" => $memberuuid
            );
            $postData = array(
                "email" => $email
            );
            return $this->makeRequest("PATCH",$this->component,"updateMemberEmail",$qString,$postData);
        }

        /* Validate Login */
        function validateMemberLogin($userid,$password,$recordlogin) {
            $qString = array();
            $postData = array(
                "userid" => $userid,
                "password" => $password,
				"recordlogin" => $recordlogin
            );
            return $this->makeRequest("POST",$this->component,"ValidateMemberLogin",$qString,$postData);
        }

        /* Validate Token */
        function validateMemberToken($member_number,$userid,$token,$recordlogin) {
            $qString = array();
            $postData = array(
                "member_number" => $member_number,
                "userid" => $userid,
                "token" => $token,
				"recordlogin" => $recordlogin
            );
            return $this->makeRequest("POST",$this->component,"validateMemberToken",$qString,$postData);
        }

        /* Generate SSO */
        function generateSSO($sso_token,$memberuuid,$userid,$token) {
            $rTimestamp = $this->mgDateFormatter();
            $stringToSign = $memberuuid."-".strtolower($userid)."-".$token."-".$rTimestamp['dateTime'];
            $hash = hash("sha256",$stringToSign);
            $sso = $sso_token."-".$rTimestamp['dateTime']."-".$hash;
            return $sso;
        }

        /* Password Reset */
        function passwordReset($email) {
            $qString = array();
            $postData = array(
                "email" => $email
            );
            return $this->makeRequest("POST",$this->component,"passwordReset",$qString,$postData);
        }

    }

?>