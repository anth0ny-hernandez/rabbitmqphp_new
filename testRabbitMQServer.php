<?php
require_once('rabbitMQLib.inc');

// Include database interaction functions (e.g., doRegister)
require_once('dbProcessor.php');

function requestProcessor($request) {
    echo "received request".PHP_EOL;
    var_dump($request);
    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    switch ($request['type']) {
        case "register":
            return doRegister($request['username'], $request['password']);
        // Add more case handlers (login, logout, etc.) as needed
    }
    return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$server->process_requests('requestProcessor');
?>

