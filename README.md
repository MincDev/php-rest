# php-rest
This library is a basic helper to assist with creating Restful API's using php

## Installation

Install php-rest through composer
```
composer require mincdev/php-rest
```

## Getting Started

Getting started is easy. Follow the steps below

### 1. Setup your router

```
<?php

require dirname(dirname(__FILE__)) . '/MyAPI.php';

try {
    $API = new API\MyAPI($_REQUEST);
    echo $API->processAPI();
} catch (Exception $e) {
    echo json_encode([
        \PHPRest\Constants\Config::RESPONSE_STATUS_KEY => false, 
        \PHPRest\Constants\Config::RESPONSE_MESSAGE_KEY => $e->getMessage()
    ]);
}
```

### 2. Setup your calls

```
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
```

## How do I call my API?

Once you have done the above, make sure to check the .htaccess file and adjust the route as needed. You'll probably want to change the `my_api` part. 
Once that is done, you can call your API as follows:

```
https://api.your-domain.com/json/my_api/sayHello?name=YourName
```

The API will produce the following JSON if setup successfully:

```
{
    "successful": true,
    "greeting": "Hello, YourName"
}
```

#### Note that the above is the call and output for the example classes added to this repository.

