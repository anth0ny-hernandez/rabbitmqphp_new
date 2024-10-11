<?php
require_once('rabbitMQLib.inc');

// Include database interaction functions (e.g., doRegister)
require_once('dbProcessor.php');

function requestProcessor($request) {
    echo "Received Request".PHP_EOL;
    var_dump($request);
    if (!isset($request['type'])) {
        return array("status" => "fail", "message" => "ERROR: Unsupported message type");
    }

    switch ($request['type']) {
        case "login":
            return doLogin($request['username'], $request['password']);
        case "register":
            return doRegister($request['username'], $request['password']);
        // Add cases for other actions, e.g. "logout" or "validate_session"
    }

    return array("status" => "fail", "message" => "Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$server->process_requests('requestProcessor');
?>

