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
<<<<<<< HEAD
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
=======
        case "login":
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            // $result = doLogin($request['username'], $request['password']);
            // echo "Sending response for login: $result\n";  // Debugging output
            return $result;
>>>>>>> b9058052c2c4186884f62a449d4d974e71d07225
        case "register":
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
<<<<<<< HEAD
            return $result;

=======
            // $result = doRegister($request['username'], $request['password']);
            // echo "Sending response for registration: $result\n";  // Debugging output
            return $result;
>>>>>>> b9058052c2c4186884f62a449d4d974e71d07225
        default:
            return "ERROR: unsupported message type";
    }
}

// Create a server that listens for requests from clients
$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "RabbitMQ Server is running and waiting for requests...\n";
$server->process_requests('requestProcessor');

// Close the database connection
<<<<<<< HEAD
// $conn->close();
=======
$conn->close();
>>>>>>> b9058052c2c4186884f62a449d4d974e71d07225
?>

