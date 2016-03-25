<?php

    echo "Running the CentralIndex PHP bindings test suite.\n".
        "If you're trying to use the CentralIndex PHP bindings you'll probably want ".
        "to require('lib/CentralIndex.php'); instead of this file\n";

    $centralIndex_autoload_path = __DIR__.'/../vendor/autoload.php';
    if(file_exists($centralIndex_autoload_path)){
        require_once $centralIndex_autoload_path;
    }

    function authorizeFromEnv()
    {
        $apiPublicKey = getenv('ChamberOfCommerce_API_PUBLIC_KEY');
        $apiPrivateKey = getenv('ChamberOfCommerce_API_PRIVATE_KEY');
        $apiEnvironemnt = getenv('ChamberOfCommerce_API_ENVIRONMENT');
        if (!$apiPublicKey)
            $apiPublicKey = "";
        if (!$apiPrivateKey)
            $apiPrivateKey = "";
        if (!$apiEnvironemnt)
            $apiEnvironemnt = "sandbox";

        CentralIndex::setApiCredentials($apiPublicKey, $apiPrivateKey);
        CentralIndex::setApiEnvironment($apiEnvironemnt);

    }

// Throw an exception on any error
    function exception_error_handler($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    set_error_handler('exception_error_handler');
    error_reporting(E_ALL | E_STRICT);
    require_once( dirname( __FILE__ ) . '/../lib/CentralIndex.php' );
    require_once( dirname( __FILE__ ) . '/CentralIndex/TestCase.php' );
