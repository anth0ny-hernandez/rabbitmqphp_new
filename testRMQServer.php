#!/usr/bin/php
<?php
require_once('dbListener.php');
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    // Process the request by calling dbListener's functions
    //$response = databaseProcessor($request);
    $response = processRequest($request);

    if ($response){
        echo "Server processed request\n";
    }

}

// Start RabbitMQ server to handle requests
$frontServer = new rabbitMQServer("testRabbitMQ.ini", "testServer");
echo "RabbitMQ Front Server is running and waiting for requests...\n";
$frontServer->process_requests('requestProcessor');
?>
