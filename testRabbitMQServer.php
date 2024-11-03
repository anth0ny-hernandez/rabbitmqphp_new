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
            $result = $dbClient->send_request($request);
            var_dump($result);
            return $result;

          // directs register process
        case "register":
            // creates new client to establish new connection to db's own server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            return $result;
        
        
        case "searchRecipe":
            // Route recipe search requests to the DMZ server
            $dmzClient = new rabbitMQClient("dmzConfig.ini", "dmzServer");
            $result = $dmzClient->send_request($request);
            return $result;

        case "dietRestrictions":
            // Route dietary restrictions requests to the database server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            return $result;
        
        case "getDietRestrictions":
            $session_token = $request['session_token'];

            // Find the user ID using the session token
            $userQuery = "SELECT id FROM accounts WHERE session_token = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param("s", $session_token);
            $userStmt->execute();
            $userResult = $userStmt->get_result();

            if ($userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                $user_id = $user['id'];

                // Retrieve dietary restrictions
                $prefQuery = "SELECT dietaryRestrictions, allergyType, otherRestrictions FROM preferences WHERE id = ?";
                $prefStmt = $conn->prepare($prefQuery);
                $prefStmt->bind_param("i", $user_id);
                $prefStmt->execute();
                $prefResult = $prefStmt->get_result();

                if ($prefResult->num_rows > 0) {
                    $preferences = $prefResult->fetch_assoc();
                    return array_merge(["success" => true], $preferences);
                } else {
                    return ["success" => false, "message" => "No dietary restrictions found."];
                }
            } else {
                return ["success" => false, "message" => "User not found."];
            }


        
        

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

