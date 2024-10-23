#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// function doLogin($credits) {
//     // Create a server that listens for requests from clients
//     echo "//////////////////////";
//     echo "\nNow connecting to database listener...\n";
//     $dbServer = new rabbitMQServer("testDB_RMQ.ini", "dbConnect");

//     echo "Connection successful, now sending info...\n";
//     $dbServer->process_requests('requestProcessor');
// }

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
            // $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            // echo "Sending to alvee...\n";
            // $result = $dbClient->send_request($request);
            // echo "Receiving from alvee...\n";
            // var_dump($result);
            // doLogin($request);

            // Create a server that listens for requests from clients
            echo "//////////////////////";
            echo "\nNow connecting to database listener...\n";
            $dbServer = new rabbitMQServer("testDB_RMQ.ini", "dbConnect");

            echo "Connection successful, now sending info...\n";
            $dbServer->process_requests('doLogin');
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
$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "RabbitMQ Server is running and waiting for requests...\n";
$server->process_requests('requestProcessor');

// Close the database connection
// $conn->close();
?>

