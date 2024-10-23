#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    switch ($request['type']) {
        // directs the login process
        case "login":
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testRabbitMQ.ini", "testServer");
            echo "Sending to alvee...\n";
            $result = $dbClient->send_request($request);
            echo "Receiving from alvee...\n";
            var_dump($result);
            return $result;

          // directs register process
        case "register":
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testRabbitMQ.ini", "testServer");
            $result = $dbClient->send_request($request);
            return $result;

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

