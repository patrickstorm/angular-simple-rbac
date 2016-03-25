<?php

    /**
     * Base class for CentralIndex test cases, provides some utility methods for creating
     * objects.
     */
    abstract class CentralIndexTestCase extends PHPUnit_Framework_TestCase
    {


        public function setUp(){
            authorizeFromEnv();
        }

        public function tearDown(){

        }

        public function getTestCredentials($key = null){
            $file = getcwd().'/../config/test.credentials.php';
            $credentials = [];
            if(file_exists($file)){
                $credentials = include($file);
            }
            return $credentials;
        }

    }