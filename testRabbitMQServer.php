<?php
require_once('rabbitMQLib.inc');

// Include database interaction functions (e.g., doRegister)
require_once('dbProcessor.php');

function requestProcessor($request) {
    echo "Received Request".PHP_EOL;
    var_dump($request);
    if (!isset($request['type'])) {
        return array("status" => "fail", "message" => "ERROR: Unsupported message type");
function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
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
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            // $result = doLogin($request['username'], $request['password']);
            // echo "Sending response for login: $result\n";  // Debugging output
            return $result;
        case "register":
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            // $result = doRegister($request['username'], $request['password']);
            // echo "Sending response for registration: $result\n";  // Debugging output
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
$conn->close();
?>

