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