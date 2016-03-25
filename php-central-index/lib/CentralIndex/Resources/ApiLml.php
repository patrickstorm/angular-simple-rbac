<?php

    class CentralIndex_ApiLml extends CentralIndex_ApiResource{



        /**
         * Get Place Details
         * Provides a snapshot of publicly available information on record for the identified listing.
         *
*@param  array $options          a hash of filter options
         * @param  string $apiPublicKey  [optional public key]
         * @param  string $apiPrivateKey [optional private key]
         *
         * @return CentralIndex Places Object
         *      status String(255), required
         *
                'id' Int
         *
                'url' Text(no limit) "http://centralindex.com/b/joe-spizza-123456789"
         *
                'password' String(255) Random password for the user_email provided.
         *
                'errors' Array of Text(no limit) ["Invalid country"] Only present on failure.
         *
         *
         *
         *
         *
         */
        private static $token = "56fc084b3979b36b8743f45db7452ad6";

        public static function order($data = [], $apiPublicKey=null, $apiPrivateKey=null){
            $class = get_class();
            $path = array("lml",self::$token,"order");
            return self::_post($class, $path, [], $data, $apiPublicKey, $apiPrivateKey, false);
        }

        public static function update($data = [], $apiPublicKey=null, $apiPrivateKey=null){
            $class = get_class();
            $path = array("lml",self::$token,"update");
            return self::_put($class, $path, [], $data, $apiPublicKey, $apiPrivateKey, false);
        }

        public static function cancel($data = [], $apiPublicKey=null, $apiPrivateKey=null){
            $class = get_class();
            $path = array("lml",self::$token,"cancel",$data['business_id']);
            return self::_delete($class, $path, [], $data, $apiPublicKey, $apiPrivateKey, false);
        }

    }