<?php

namespace API;

use Exception;
use PHPRest\CoreAPI;
require_once dirname(__FILE__) . '/vendor/autoload.php';

class MyAPI extends CoreAPI {
    
    public function __construct($endpoint) {
        parent::__construct($endpoint);
        
        // Uncomment below to start using basic authentication
        // $basicAuth = parent::getBasicAuthCredentials();

        // TODO:: Do any type of validation using the basic auth details against your database. 
        // If authorization fails, throw an error with "throw new Exception("401 Unauthorized", 401);"
    }
    
    protected function sayHello($params) 
    {
        $name = isset($params['name']) ? $params['name'] : '';

        if (empty($name)) {
            throw new Exception('Name is required!', 412);
        }

        return [
            \PHPRest\Constants\Config::RESPONSE_STATUS_KEY => true,
            "greeting" => sprintf("Hello, %s", $name)
        ];
    }
}