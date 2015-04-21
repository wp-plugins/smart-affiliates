<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Paypal')) {

    class Saff_Paypal {
        
        const USE_PROXY = false;
        const PROXY_HOST = '127.0.0.1';
        const PROXY_PORT = '8080';

        var $api_username = null;
        var $api_password = null;
        var $api_signature = null;
        var $receiver_type = 'EmailAddress';
        var $email_subject = 'EmailSubject';
        var $subject = '';
        var $version = '98.0';
        var $currency = 'USD';
        var $api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
        //VAR $api_endpoint = 'https://api-3t.paypal.com/nvp';

        function __construct() {
            $this->api_username = trim(get_option('saff_paypal_api_username', ''));
            $this->api_password = trim(get_option('saff_paypal_api_password', ''));
            $this->api_signature = trim(get_option('saff_paypal_api_signature', ''));
            
            $is_paypal_sandbox = get_option('saff_is_paypal_sandbox');
            if($is_paypal_sandbox == 'yes') {
                $this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
            } else {
                $this->api_endpoint = 'https://api-3t.paypal.com/nvp';
            }

            add_action( 'wp_ajax_get_paypal_balance', array( $this, 'get_paypal_balance' ) );
        }

        function get_paypal_balance() {

            check_ajax_referer( SAFF_AJAX_SECURITY, 'security' );

            $paypal_response = $this->get_balance();

            $response = array();

            if ( ! empty( $paypal_response['ACK'] ) && $paypal_response['ACK'] == 'Success' ) {

                $response = array(
                                'amount' => ( ! empty( $paypal_response['L_AMT0'] ) ) ? $paypal_response['L_AMT0'] : 0,
                                'currency' => ( ! empty( $paypal_response['L_CURRENCYCODE0'] ) ) ? $paypal_response['L_CURRENCYCODE0'] : ''
                            );

            }

            echo json_encode( $response );
            die();

        }

        public function process_paypal_mass_payment($affiliates, $currency) {
            $response = $this->make_payment( $affiliates, $currency );
            return $response;
        }

        function get_balance() {
            $nvp_str = '';
            $nvp_str .= $this->get_nvp_header();
            $result = $this->hash_call('GetBalance', $nvp_str);
            
            return $result;
        }

        function make_payment($affiliates, $currency) {

            
            $result = null;
            if (count($affiliates) > 0) {
                $nvp_str = '';
                $j = 0;
                $this->currency = $currency;
                // @TODO: encode data in nvpstr
                foreach ($affiliates as $key => $affiliate) {
                    if(isset($affiliate['email']) && $affiliate['email'] != '' && isset($affiliate['amount']) && $affiliate['amount'] != 0) {
                        $receiver_mail = urlencode($affiliate['email']);
                        $amount = urlencode(floatval($affiliate['amount']));
                        $unique_id = urlencode($affiliate['unique_id']);
                        $note = urlencode($affiliate['note']);
                        $nvp_str .="&L_EMAIL$j=$receiver_mail&L_Amt$j=$amount&L_UNIQUEID$j=$unique_id&L_NOTE$j=$note";
                        $j++;
                    }
                }
                $nvp_str .="&EMAILSUBJECT=$this->email_subject&RECEIVERTYPE=$this->receiver_type&CURRENCYCODE=$this->currency";
                
                $nvp_header = $this->get_nvp_header();
                $nvp_str = $nvp_header . $nvp_str;

                
                $result = $this->hash_call('MassPay', $nvp_str);
            }
            
            return $result;
        }

        function get_nvp_header() {

            if (!empty($this->api_username) && !empty($this->api_password) && !empty($this->api_signature) && !empty($this->subject)) {
                $AuthMode = "THIRDPARTY";
            } else if (!empty($this->api_username) && !empty($this->api_password) && !empty($this->api_signature)) {
                $AuthMode = "3TOKEN";
            } else if (!empty($this->subject)) {
                $AuthMode = "FIRSTPARTY";
            }

            switch ($AuthMode) {

                case "3TOKEN" :
                    $nvpHeader = "&PWD=" . urlencode($this->api_password) . "&USER=" . urlencode($this->api_username) . "&SIGNATURE=" . urlencode($this->api_signature);
                    break;
                case "FIRSTPARTY" :
                    $nvpHeader = "&SUBJECT=" . urlencode($this->subject);
                    break;
                case "THIRDPARTY" :
                    $nvpHeader = "&PWD=" . urlencode($this->api_password) . "&USER=" . urlencode($this->api_username) . "&SIGNATURE=" . urlencode($this->api_signature) . "&SUBJECT=" . urlencode($this->subject);
                    break;
            }
            
            return $nvpHeader;
        }

        function hash_call($methodName, $nvpStr) {
            //declaring of global variables
            

            //echo $API_Endpoint;
            //setting the curl parameters.
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_endpoint);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);

            //turning off the server and peer verification(TrustManager Concept).
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
            //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
            if (self::USE_PROXY)
                curl_setopt($ch, CURLOPT_PROXY, self::PROXY_HOST . ":" . self::PROXY_PORT);

            //check if version is included in $nvpStr else include the version.
            if (strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
                $nvpStr = "&VERSION=" . urlencode($this->version) . $nvpStr;
            }

            $nvpreq = "METHOD=" . $methodName . $nvpStr;
            
            //setting the nvpreq as POST FIELD to curl
            curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

            //getting response from server
            $response = curl_exec($ch);
            
            
            //convrting NVPResponse to an Associative Array
            $nvpResArray = $this->de_format_nvp($response);
            $nvpReqArray = $this->de_format_nvp($nvpreq);
            
            $_SESSION['nvpReqArray'] = $nvpReqArray;

            if (curl_errno($ch)) {
                // moving to display page to display curl errors
                $_SESSION['curl_error_no'] = curl_errno($ch);
                $_SESSION['curl_error_msg'] = curl_error($ch);
                $location = "APIError.php";
                header("Location: $location");
            } else {
                //closing the curl
                curl_close($ch);
            }

            return $nvpResArray;
        }

        /** This function will take NVPString and convert it to an Associative Array and it will decode the response.
         * It is usefull to search for a particular key and displaying arrays.
         * @nvpstr is NVPString.
         * @nvpArray is Associative Array.
         */
        function de_format_nvp($nvpstr) {

            $intial = 0;
            $nvpArray = array();

            while (strlen($nvpstr)) {
                //postion of Key
                $keypos = strpos($nvpstr, '=');
                //position of value
                $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

                /* getting the Key and Value values and storing in a Associative Array */
                $keyval = substr($nvpstr, $intial, $keypos);
                $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
                //decoding the respose
                $nvpArray[urldecode($keyval)] = urldecode($valval);
                $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
            }
            
            return $nvpArray;
        }

    }

}

return new Saff_Paypal();          