#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');
require_once('dbListener.php'); // Include dbListener.php for processing

function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    switch ($request['type']) {
        // Direct the login process to dbListener
        case "login":
            echo "Processing login in dbListener...\n";
            $response = databaseProcessor($request); // Call the function from dbListener.php
            return $response;

        // Direct the registration process to dbListener
        case "register":
            echo "Processing registration in dbListener...\n";
            $response = databaseProcessor($request); // Call the function from dbListener.php
            return $response;

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
