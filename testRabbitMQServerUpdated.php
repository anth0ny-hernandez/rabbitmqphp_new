#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');
require_once('dbListenerUpdated.php');

function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);
    $username = $request['username'];
    $password = $request['password'];

    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    switch ($request['type']) {
        // directs the login process
        case "login":
            // creates new client to establish new connection to db's own server
            echo "Sending to alvee...\n";
            return processLogin($username, $password);
          // directs register process
        case "register":
            // creates new client to establish new connection to db's own server
            echo "Sending to alvee...\n";
            return processRegistration($username, $password);

        default:
            return "ERROR: unsupported message type";
    }
}

// Create a server that listens for requests from clients
$frontServer = new rabbitMQServer("testRabbitMQ.ini", "testServer");
echo "RabbitMQ Front Server is running and waiting for requests...\n";

// Process incoming requests using the requestProcessor function
$frontServer->process_requests('requestProcessor');
?>

