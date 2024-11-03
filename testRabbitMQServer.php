#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');
// function checkRecipeCache($query) {
//     $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");

//     // Prepare the request to check cache
//     $request = [
//         'type' => 'checkCache',
//         'query' => $query
//     ];

//     // Send request to dbListener and get cached results
//     return $dbClient->send_request($request);
// }

// // Function to cache new recipes in the database
// function cacheRecipes($query, $recipes) {
//     $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");

//     // Prepare the request to cache the recipes
//     $request = [
//         'type' => 'cacheRecipes',
//         'query' => $query,
//         'recipes' => $recipes
//     ];

//     // Send request to dbListener to cache the recipes
//     $dbClient->send_request($request);
// }

// // Function to handle the search recipe process
// function searchRecipe($request) {
//     $query = $request['label'] ?? 'recipe';

//     // Step 1: Check if recipes are already cached
//     $cachedRecipes = checkRecipeCache($query);

//     if (!empty($cachedRecipes['hits'])) {
//         // Return cached recipes if available
//         return $cachedRecipes;
//     }

//     // Step 2: No cached data, so request from DMZ
//     $dmzClient = new rabbitMQClient("dmzConfig.ini", "dmzServer");

//     $dmzRequest = [
//         'type' => 'searchRecipe',
//         'label' => $query,
//         'healthLabels' => $request['healthLabels'] ?? null,
//         'cuisineType' => $request['cuisineType'] ?? null,
//         'mealType' => $request['mealType'] ?? null,
//         'ENERC_KCAL' => $request['ENERC_KCAL'] ?? null
//     ];

//     $dmzResponse = $dmzClient->send_request($dmzRequest);

//     if (isset($dmzResponse['hits']) && !empty($dmzResponse['hits'])) {
//         // Step 3: Cache the new recipes in the database
//         cacheRecipes($query, $dmzResponse['hits']);
//     }

//     // Return the response from the DMZ (API results)
//     return $dmzResponse;
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
                $dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
                echo "Connected to database...\n";
    
                $result = $dbClient->send_request($request);
                echo "Asking database if recipe(s) exist within it...\n";
    
                // checks that recipe(s) do exist, otherwise send a request to DMZ
                if(!$result) {
                    $dmzClient = new rabbitMQClient("dmzConfig.ini", "dmzServer");
                    $result = $dmzClient->send_request($request);
                    // if even DMZ returns no matches, then let the user know
                    if(!$result) {
                        echo "Sorry, no recipes match that! Returning no matches...\n";
                        return $result;
                    } else {
                        echo "Recipe(s) found! Updating the database now...\n";
                        $request['type'] = 'insertRecipe'; // modify request parameter
                        $result = $dbClient->send_request($request);
                        return $result;
                    }
                } else {
                    echo "Recipes found! Sending back to front-end user...\n";
                    return $result;
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

