<?php

    abstract class CentralIndex
    {
        public static $apiPublicKey;
        public static $apiPrivateKey;
        public static $apiBase = 'http://centralindex.com';
        public static $apiEnvironemnt = 'production'; // sandbox/production
        public static $apiVersion = null;
        public static $debugPath = "curl.txt";
        public static $debug = false;

        public static function isDebug(){
            return self::$debug;
        }

        public static function enableDebug(){
            self::$debug = true;
        }

        public static function disableDebug(){
            self::$debug = true;
        }

        public static $verifySslCerts = true;
        public static $environmentSettings = array(
            'production' => array(
                'baseUrl' => 'http://centralindex.com',
                'ip' => 'https://198.49.67.107'
            ),
            'sandbox' => array(
                'baseUrl' => 'http://centralindex.com',
                'ip'=> 'http://198.49.67.109'
            ),
        );

        const VERSION = '1.0.0';

        public static function getApiKey(){
            return self::$apiKey;
        }

        public static function getApiBaseUrl($environment){
            return self::$environmentSettings[$environment]['baseUrl'];
        }

        public static function setApiCredentials($apiPublicKey, $apiPrivateKey){
            self::$apiPublicKey = $apiPublicKey;
            self::$apiPrivateKey = $apiPrivateKey;
        }
        public static function setApiPublicKey($apiPublicKey){
            self::$apiPublicKey = $apiPublicKey;

        }

        public static function setApiPrivateKey($apiPrivateKey){
            self::$apiPrivateKey = $apiPrivateKey;
        }

        public static function setApiEnvironment($apiEnvironemnt){
            self::$apiEnvironemnt = $apiEnvironemnt;
        }

        public static function getApiVersion(){
            if (!self::$apiVersion){
                throw new CentralIndex_AuthenticationError('No API Version provided.  (HINT: set your API key using "CentralIndex::setApiVersion(<API-VERSION>)".  You can generate API keys from the CentralIndex web interface.  See https://trustedsearch.org/api for details, or email support@trustedsearch.org if you have any questions.');
            }
            return self::$apiVersion;
        }

        public static function setApiVersion($apiVersion){
            self::$apiVersion = $apiVersion;
        }

        public static function getVerifySslCerts() {
            return self::$verifySslCerts;
        }

        public static function setVerifySslCerts($verify) {
            self::$verifySslCerts = $verify;
        }
    }