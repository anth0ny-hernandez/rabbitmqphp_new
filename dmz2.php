<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Function to fetch recipes from the external API
function searchRecipe($request) {
    $query = $request['label'] ?? 'recipe';
    $params = [
        'type' => 'public',
        'q' => $query,
        'app_id' => '4577783c', 
        'app_key' => '2ebd6b0aa43312e5f01f2077882ca32f',
        'health' => $request['healthLabels'] ?? null,
        'cuisineType' => $request['cuisineType'] ?? null,
        'mealType' => $request['mealType'] ?? null,
        'calories' => $request['ENERC_KCAL'] ?? null,
    ];
    
    $params = array_filter($params); // Remove null values
    $url = "https://api.edamam.com/api/recipes/v2?" . http_build_query($params);

    // Set up cURL request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = ['Edamam-Account-User: AlveeJalal'];
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    curl_close($curl);

    // Decode the JSON response
    $data = json_decode($response, true);

    if (!isset($data['hits']) || empty($data['hits'])) {
        return ["error" => "No recipes found"];
    }

    // Format the data to match the database structure
    $recipes = [];
    foreach ($data['hits'] as $hit) {
        $recipe = $hit['recipe'];
        $recipes[] = [
            'label' => $recipe['label'],
            'image' => $recipe['image'] ?? null,
            'url' => $recipe['url'],
            'healthLabels' => $recipe['healthLabels'] ?? [],
            'ENERC_KCAL' => $recipe['calories'] ?? 0,
            'ingredientLines' => $recipe['ingredientLines'] ?? [],
            'calories' => $recipe['calories'] ?? 0,
            'cuisineType' => $recipe['cuisineType'] ?? [],
            'mealType' => $recipe['mealType'] ?? [],
            'totalNutrients' => [
                'FAT' => $recipe['totalNutrients']['FAT']['quantity'] ?? 0,
                'CHOCDF' => $recipe['totalNutrients']['CHOCDF']['quantity'] ?? 0,
                'FIBTG' => $recipe['totalNutrients']['FIBTG']['quantity'] ?? 0,
                'SUGAR' => $recipe['totalNutrients']['SUGAR']['quantity'] ?? 0,
                'PROCNT' => $recipe['totalNutrients']['PROCNT']['quantity'] ?? 0,
                'CHOLE' => $recipe['totalNutrients']['CHOLE']['quantity'] ?? 0,
                'NA' => $recipe['totalNutrients']['NA']['quantity'] ?? 0,
                'CA' => $recipe['totalNutrients']['CA']['quantity'] ?? 0,
                'VITA_RAE' => $recipe['totalNutrients']['VITA_RAE']['quantity'] ?? 0,
                'VITC' => $recipe['totalNutrients']['VITC']['quantity'] ?? 0
            ]
        ];
    }

    return ['hits' => $recipes];
}

// Main request processor function
function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return ["error" => "Unsupported message type"];
    }

    switch ($request['type']) {
        case "searchRecipe":
            return searchRecipe($request);

        default:
            return ["error" => "Unsupported message type"];
    }
}

// Create a server that listens for requests from the main server
$server = new rabbitMQServer("dmzConfig.ini", "dmzServer");
echo "DMZ Server for Recipe Search is running and waiting for requests...\n";
$server->process_requests('requestProcessor');
?>
