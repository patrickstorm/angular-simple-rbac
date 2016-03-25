<?php

// Tested on PHP 5.2, 5.3, 5.4

// This snippet (and some of the curl code) due to the Facebook SDK.
    if (!function_exists('curl_init')) {
        throw new Exception('CentralIndex needs the CURL PHP extension.');
    }
    if (!function_exists('json_decode')) {
        throw new Exception('CentralIndex needs the JSON PHP extension.');
    }
    if (!function_exists('mb_detect_encoding')) {
        throw new Exception('CentralIndex needs the Multibyte String PHP extension.');
    }

//Load autoload file if exists
    $centralIndex_autoload_path = __DIR__.'/../vendor/autoload.php';
    if(file_exists($centralIndex_autoload_path)){
        require_once $centralIndex_autoload_path;
    }

// CentralIndex singleton
    require_once(dirname(__FILE__) . '/CentralIndex/CentralIndex.php');

// Utilities
    require_once(dirname(__FILE__) . '/CentralIndex/Util.php');
    require_once(dirname(__FILE__) . '/CentralIndex/Util/Set.php');

// Errors
    require_once(dirname(__FILE__) . '/CentralIndex/Errors/Error.php');
    require_once(dirname(__FILE__) . '/CentralIndex/Errors/ApiError.php');
    require_once(dirname(__FILE__) . '/CentralIndex/Errors/ApiConnectionError.php');
    require_once(dirname(__FILE__) . '/CentralIndex/Errors/AuthenticationError.php');
    require_once(dirname(__FILE__) . '/CentralIndex/Errors/InvalidRequestError.php');
    require_once(dirname(__FILE__) . '/CentralIndex/Errors/RequestBindingException.php');

// Plumbing

    require_once(dirname(__FILE__) . '/CentralIndex/Object.php');
    require_once(dirname(__FILE__) . '/CentralIndex/ApiRequestor.php');
    require_once(dirname(__FILE__) . '/CentralIndex/ApiResource.php');
    require_once(dirname(__FILE__) . '/CentralIndex/AttachedObject.php');

// CentralIndex API Resources
    require_once(dirname(__FILE__) . '/CentralIndex/Resources/ApiLml.php');