<?php

    abstract class ChamberOfCommerce_Util{

        public static function isList($array){
            if (!is_array($array))
                return false;

            // TODO: this isn't actually correct in general, but it's correct given CentralIndex's responses
            foreach (array_keys($array) as $k) {
                if (!is_numeric($k))
                    return false;
            }
            return true;
        }

        public static function convertChamberOfCommerceObjectToArray($values){
            $results = array();
            foreach ($values as $k => $v) {
                // FIXME: this is an encapsulation violation
                if ($k[0] == '_') {
                    continue;
                }
                if ($v instanceof CentralIndex_Object) {
                    $results[$k] = $v->__toArray(true);
                }
                else if (is_array($v)) {
                    $results[$k] = self::convertChamberOfCommerceObjectToArray($v);
                }
                else {
                    $results[$k] = $v;
                }
            }
            return $results;
        }

        public static function convertToChamberOfCommerceObject($resp, $apiKey){
            $types = array(
                'token' => 'ChamberOfCommerce_Token',
                'local_business' => 'ChamberOfCommerce_LocalBusiness',
                'business_snapshots' => 'ChamberOfCommerce_BusinessSnapshots',
            );

            if (self::isList($resp)) {
                $mapped = array();
                foreach ($resp as $i)
                    array_push($mapped, self::convertToChamberOfCommerceObject($i, $apiKey));
                return $mapped;
            } else if (is_array($resp)) {
                if (isset($resp['object']) && is_string($resp['object']) && isset($types[$resp['object']]))
                    $class = $types[$resp['object']];
                else
                    $class = 'CentralIndex_Object';
                return CentralIndex_Object::scopedConstructFrom($class, $resp, $apiKey);
            } else {
                return $resp;
            }
        }
    }