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
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            echo "Sending to alvee...\n";
            $result = $dbClient->send_request($request);
            echo "Receiving from alvee...\n";
            var_dump($result);
            return $result;

          // directs register process
        case "register":
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            return $result;

        default:
            return "ERROR: unsupported message type";
    }
}

// Create a server that listens for requests from clients
$frontserver = new rabbitMQServer("testRabbitMQ.ini", "testServer");
$dbServer = new rabbitMQServer("testDB_RMQ.ini", "dbConnect");
echo "RabbitMQ Server is running and waiting for requests...\n";
$frontserver->process_requests('requestProcessor');
$dbServer->process_requests('requestProcessor');
// Close the database connection
// $conn->close();
?>

