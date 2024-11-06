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
        
        
        case "insertRecipe":
            // Route recipe search requests to the DMZ server
            $dmzClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dmzClient->send_request($request);
            var_dump($result);
            return $result;

        case "dietRestrictions":
            // Route dietary restrictions requests to the database server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            return $result;
        
        case "getDietRestrictions":
            // Route the getDietRestrictions request to the database server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $result = $dbClient->send_request($request);
            return $result;
        
        case "recommendRecipes":
            // Check for user preferences through the database server
            $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
            $preferencesRequest = [
                "type" => "getUserPreferences",
                "session_token" => $request['session_token']
            ];
            
            $preferencesResponse = $dbClient->send_request($preferencesRequest);

            // Check if user has preferences saved; if not, request random recipes
            if ($preferencesResponse['success']) {
                // Forward preferences to DMZ for recommendation
                $dmzClient = new rabbitMQClient("dmzConfig.ini", "dmzServer");
                $recommendRequest = [
                    "type" => "recommendRecipes",
                    "preferences" => $preferencesResponse
                ];
                return $dmzClient->send_request($recommendRequest);
            } else {
                // No preferences found, get random recipes
                $dmzClient = new rabbitMQClient("dmzConfig.ini", "dmzServer");
                $randomRequest = [
                    "type" => "searchRecipe",
                    "label" => "recipe"  // Generic query to get random recipes
                ];
                return $dmzClient->send_request($randomRequest);
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

