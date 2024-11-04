<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function recommendRecipes($preferences) {
    // Define parameters for the Edamam API request based on preferences
    $params = array(
        'type' => 'public',
        'app_id' => '4577783c', 
        'app_key' => '2ebd6b0aa43312e5f01f2077882ca32f',
        'health' => $preferences['dietaryRestrictions'] ?? null,
        'q' => $preferences['otherRestrictions'] ?? 'recipe'  // Default query if no specific preference
    );

    // Filter out empty parameters
    $params = array_filter($params);

    // Build the URL for the API request
    $url = "https://api.edamam.com/api/recipes/v2?" . http_build_query($params);

    echo "Request URL: $url\n";  // Debugging output for URL

    // Set up cURL request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Add custom header for account verification
    $headers = [
        'Edamam-Account-User: AlveeJalal',
    ];
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // Execute the request and decode the response
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    // Check if 'hits' contains data
    if (!isset($data['hits']) || empty($data['hits'])) {
        return ["error" => "No recipes found based on preferences"];
    }

    return $data;
}


function searchRecipe($request) {
    // Define parameters for the request, ensuring 'q' is present
    $params = array(
        'type' => 'public',
        'q' => $request['label'] ?? null,  // Default to 'chicken' if no query provided
        'app_id' => '4577783c', 
        'app_key' => '2ebd6b0aa43312e5f01f2077882ca32f',
        'health' => $request['healthLabels'] ?? null,
        'cuisineType' => $request['cuisineType'] ?? null,
        'mealType' => $request['mealType'] ?? null,
        'nutrients[ENERC_KCAL]' => $request['ENERC_KCAL'] ?? null,
    );

    $params = array_filter($params);  // Remove null values
    $url = "https://api.edamam.com/api/recipes/v2?" . http_build_query($params);

    echo "Request URL: $url\n";  // Debugging output for URL

    // Set up cURL request with headers
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Add custom header for account verification
    $headers = [
        'Edamam-Account-User: AlveeJalal',
    ];
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // Execute the request and decode the response
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    
    // Check if 'hits' contains data
    if (!isset($data['hits']) || empty($data['hits'])) {
        return ["error" => "No recipes found"];
    }

    return $data;
}

function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return ["error" => "Unsupported message type"];
    }

    switch ($request['type']) {

        case "searchRecipe":
            return searchRecipe($request);  // Pass the entire request array to searchRecipe

        case "recommendRecipes":
            // Use the provided preferences for the recommendation
            return recommendRecipes($request['preferences']);

        default:
            return ["error" => "Unsupported message type"];
    }
}

// Create a server that listens for requests from clients
$server = new rabbitMQServer("dmzConfig.ini", "dmzServer");
echo "DMZ Server for Meal Planning and Recipe Search is running and waiting for requests...\n";
$server->process_requests('requestProcessor');
