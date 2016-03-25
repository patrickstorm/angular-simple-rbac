<?php

    class CentralIndex_ApiRequestor{
        public $_apiPublicKey;
        public $_apiPrivateKey;
        protected $_responseBody;
        protected $_responseBodyRaw;
        protected $_responseCode;
        protected $_responseHeaders;
        protected $_authenticate = true;

        protected $_request;

        public function __construct($apiPublicKey=null, $apiPrivateKey=null){
            $this->_apiPublicKey = $apiPublicKey;
            $this->_apiPrivateKey = $apiPrivateKey;
        }

        public function request($meth, $url, $params=array(), $body=''){
             // echo "\nPARAMS: ".json_encode($params);
             // echo "\nRESOURCE: ".$url;
             // echo "\nBODY: ".json_encode($body);

            list($rbody, $rcode, $rhead) = $this->_requestRaw($meth, $url, $params, (is_array($body)?json_encode($body):''));
                // echo "\nRespone Code: ".($rcode);

            $response = $this->_interpretResponse($rbody, $rcode, $rhead);
            return $response;
        }


        private function _requestRaw($meth, $url, $params = array(), $body = ''){
            $apiPublicKey = $this->_apiPublicKey;
            $apiPrivateKey = $this->_apiPrivateKey;
            //If authenticate is enabled. Then help out the developer by letting them know if they forgot to set api keys
            if($this->_authenticate){
                if (!$apiPrivateKey){
                    $apiPrivateKey = CentralIndex::$apiPrivateKey;
                }

                if (!$apiPrivateKey){
                    throw new CentralIndex_AuthenticationError('No API Private key provided.  (HINT: set your API key using "CentralIndex::setApiPrivateKey(<API-KEY>)".  You can generate API keys from the CentralIndex web interface.  See https://trustedsearch.org/api for details, or email support@trustedsearch.org if you have any questions.');
                }
            }

            $absUrl = $this->apiUrl($url);
            $params = self::_encodeObjects($params);

            $langVersion = phpversion();
            $uname = php_uname();
            $ua = array(
                'bindings_version' => CentralIndex::VERSION,
                'lang' => 'php',
                'lang_version' => $langVersion,
                'publisher' => 'trustedsearch',
                'uname' => $uname
            );

            $headers = array(
                'X-CentralIndex-Client-User-Agent: ' . json_encode($ua),
                'User-Agent: CentralIndex/v1 PhpBindings/' . CentralIndex::VERSION,
                'Content-Type:   application/json'
            );

            if (CentralIndex::$apiVersion){
                $headers[] = 'CentralIndex-Version: ' . CentralIndex::$apiVersion;
            }

            if($this->_authenticate){
                //Handle Authentication/Signature
                switch (CentralIndex::getApiVersion()) {
                    case '1':
                        $params = array_merge($params, array(
                                'publisher'=>CentralIndex::$apiPrivateKey,

                            )
                        );
                    # code...
                    case '2':
                        break;
                    default:
                        # code...
                        break;
                }
            }

            list($rbody, $rcode, $rhead) = $this->_curlRequest($meth, $absUrl, $headers, $params, $body);

            return array($rbody, $rcode, $rhead);
        }

        public function disableAuthentication(){
            $this->_authenticate = false;
        }

        private function _interpretResponse($rbody, $rcode, $rhead){

            $this->_responseBodyRaw = $resp = $rbody;

            try {
                $rbody = json_decode($rbody, true);
            } catch (Exception $e) {
                throw new CentralIndex_ApiError("Invalid response body from API: $resp (HTTP response code was $rcode)", $rcode, $resp);
            }
            $this->_responseHeaders = $rhead;
            $this->_responseBody = $rbody;
            $this->_responseCode = $rcode;

            if ($rcode < 200 || $rcode >= 300) {
                $this->handleApiError($rbody, $rcode, $resp);
            }
            $response = array(
                'headers' => $rhead,
                'code' => $rcode,
                'body' =>$rbody
            );
            return $response;
        }

        /**
         * Convert an api error response into the correct exception
         * @param  array $rbody the body of the response
         * @param  integer $rcode http response status code.
         * @param  string $resp  raw unencoded
         * @throws ChamberOfCommerceError
         */
        public function handleApiError($rbody, $rcode, $resp){

            if (!is_array($rbody)){
                throw new CentralIndex_ApiError("Invalid response object from API: $resp (HTTP response code was $rcode)", $rcode, $resp);
            }

            //Extract error message for error response.
            $responseObject = isset($rbody['ResponseStatus']) ? $rbody['ResponseStatus'] : null;
            $message = "(".$responseObject['ErrorCode'].") " . $responseObject['Message'];
            $errorCode = $responseObject['ErrorCode'];
            switch ($rcode) {
                case 400:
                    switch($errorCode){
                        case "RequestBindingException";
                            throw new CentralIndex_RequestBindingException($message, $rcode, $resp, $rbody);
                            break;
                        default:
                            break;
                    }
                case 404:
                    throw new CentralIndex_InvalidRequestError($message, $rcode, $resp, $rbody);
                case 401:
                    throw new CentralIndex_AuthenticationError($message, $rcode, $resp, $rbody);
                default:
                    throw new CentralIndex_ApiError($message, $rcode, $resp, $rbody);
            }
        }

        /**
         * gets a date in RFC1123 format.
         * @return [type] [description]
         */
        private function _getDate(){
            $date = new \DateTime();
            return $date->format($date::RFC1123);
        }

        /**
         * Make a curl request
         * @param  string $meth    the type of method [get, post, put, delete]
         * @param  string $absUrl  the url to make the request to.
         * @param  array $headers headers to be sent in requrest.
         * @param  array $params  query string params.
         * @param  string $body    body to be sent if POST/PUT
         * @return array          array($body, $code, $header)
         */
        private function _curlRequest($meth, $absUrl, $headers, $params, $body = ''){
            $curl = curl_init();
            $meth = strtolower($meth);
            $opts = array();
            if ($meth == 'get') {
                $opts[CURLOPT_HTTPGET] = 1;
                if (count($params) > 0) {
                    $encoded = self::encode($params);
                    $absUrl = "$absUrl?$encoded";
                }
            }else if ($meth == 'post') {

                if (count($params) > 0) {
                    $encoded = self::encode($params);
                    $absUrl = "$absUrl?$encoded";
                }
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = self::encode($body);


            }else if ($meth == 'put')  {
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if (count($params) > 0) {
                    $encoded = self::encode($params);
                    $absUrl = "$absUrl?$encoded";
                }
                if(!empty($body)){
                    $opts[CURLOPT_POSTFIELDS] = self::encode($body);
                }
            }else if ($meth == 'delete')  {
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                if (count($params) > 0) {
                    $encoded = self::encode($params);
                    $absUrl = "$absUrl?$encoded";
                }
            } else {
                throw new CentralIndex_ApiError("Unrecognized method $meth");
            }

            $absUrl = self::utf8($absUrl);
            $opts[CURLOPT_URL] = $absUrl;
            $opts[CURLOPT_RETURNTRANSFER] = true;
            $opts[CURLOPT_CONNECTTIMEOUT] = 30;

            $opts[CURLOPT_RETURNTRANSFER] = true;
            $opts[CURLOPT_HTTPHEADER] = $headers;
            $opts[CURLOPT_HEADER] = 1;


            if (!CentralIndex::$verifySslCerts){
                $opts[CURLOPT_SSL_VERIFYPEER] = false;
            }

            if(CentralIndex::isDebug()){
                $opts[CURLOPT_VERBOSE] = true;
                $curl_log = fopen(CentralIndex::$debugPath, 'a+');
                $opts[CURLOPT_STDERR] = $curl_log;
            }

            curl_setopt_array($curl, $opts);
            $rbody = curl_exec($curl);
            $errno = curl_errno($curl);

            if ($errno == CURLE_SSL_CACERT ||
                $errno == CURLE_SSL_PEER_CERTIFICATE ||
                $errno == 77 // CURLE_SSL_CACERT_BADFILE (constant not defined in PHP though)
            ){
                array_push($headers, 'X-CentralIndex-Client-Info: {"ca":"using CentralIndex-supplied CA bundle"}');
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/../data/ca-certificates.crt');
                $rbody = curl_exec($curl);
            }

            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $rheader = substr($rbody, 0, $header_size);
            $rbody = substr($rbody, $header_size);

            if ($rbody === false) {
                $errno = curl_errno($curl);
                $message = curl_error($curl);
                curl_close($curl);
                $this->handleCurlError($errno, $message);
            }

            $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            return array($rbody, $rcode, $rheader);
        }

        /**
         * Handler for curl errors
         * @param  integer $errno   CURL error
         * @param  string $message curl error message
         * @throws ChamberOfCommerceError
         */
        public function handleCurlError($errno, $message){
            $apiBase = CentralIndex::$apiBase;
            switch ($errno) {
                case CURLE_COULDNT_CONNECT:
                case CURLE_COULDNT_RESOLVE_HOST:
                case CURLE_OPERATION_TIMEOUTED:
                    $msg = "Could not connect to CentralIndex ($apiBase).  Please check your internet connection and try again.  If this problem persists, you should check CentralIndex's service status at https://twitter.com/trustedsearchstatus, or let us know at support@trustedsearch.org.";
                    break;
                case CURLE_SSL_CACERT:
                case CURLE_SSL_PEER_CERTIFICATE:
                    $msg = "Could not verify CentralIndex's SSL certificate.  Please make sure that your network is not intercepting certificates.  (Try going to $apiBase in your browser.)  If this problem persists, let us know at support@trustedsearch.org.";
                    break;
                default:
                    $msg = "Unexpected error communicating with CentralIndex.  If this problem persists, let us know at support@trustedsearch.org.";
            }

            $msg .= "\n\n(Network error [errno $errno]: $message)";
            throw new CentralIndex_ApiConnectionError($msg);
        }

        public static function apiUrl($url=''){
            $apiBase = CentralIndex::getApiBaseUrl(CentralIndex::$apiEnvironemnt);
            return "$apiBase$url";
        }

        public static function utf8($value){
            if (is_string($value) && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8")
                return utf8_encode($value);
            else
                return $value;
        }

        private static function _encodeObjects($d){
            if ($d instanceof CentralIndex_ApiResource) {
                return self::utf8($d->id);
            } else if ($d === true) {
                return 'true';
            } else if ($d === false) {
                return 'false';
            } else if (is_array($d)) {
                $res = array();
                foreach ($d as $k => $v)
                    $res[$k] = self::_encodeObjects($v);
                return $res;
            } else {
                return self::utf8($d);
            }
        }

        public static function encode($arr, $prefix=null){
            if (!is_array($arr))
                return $arr;

            $r = array();
            foreach ($arr as $k => $v) {
                if (is_null($v))
                    continue;

                if ($prefix && $k && !is_int($k))
                    $k = $prefix."[".$k."]";
                else if ($prefix)
                    $k = $prefix."[]";

                if (is_array($v)) {
                    $r[] = self::encode($v, $k, true);
                } else {
                    $r[] = urlencode($k)."=".urlencode($v);
                }
            }

            return implode("&", $r);
        }
    }