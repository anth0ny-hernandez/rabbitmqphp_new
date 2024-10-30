<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function getWeeklyMealPlan($dietaryRestrictions = "", $caloriesPerMeal = 500) {
    $app_id = "4577783c"; // Replace with actual App ID
    $app_key = "2ebd6b0aa43312e5f01f2077882ca32f"; // Replace with actual App Key
    $mealPlanner = [];

    // Define meal types and days
    $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    $mealTypes = ["Breakfast", "Lunch", "Dinner"];

    foreach ($days as $day) {
        $mealPlanner[$day] = [];
        
        foreach ($mealTypes as $meal) {
            // Customize the API request
            $query = "meal";
            $url = "https://api.edamam.com/api/recipes/v2?type=public&q={$query}&app_id={$app_id}&app_key={$app_key}&calories={$caloriesPerMeal}";
            if ($dietaryRestrictions) {
                $url .= "&health={$dietaryRestrictions}";
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);
            if (!$response) {
                return ["error" => "API request failed"];
            }
            curl_close($curl);

            $data = json_decode($response, true);
            if (isset($data['hits'][0])) {
                $recipe = $data['hits'][0]['recipe'];
                $mealPlanner[$day][$meal] = [
                    "label" => $recipe['label'],
                    "url" => $recipe['url'],
                    "calories" => $recipe['calories'],
                    "image" => $recipe['image']
                ];
            } else {
                $mealPlanner[$day][$meal] = [
                    "label" => "No meal found",
                    "url" => "#",
                    "calories" => 0,
                    "image" => ""
                ];
            }
        }
    }

    return $mealPlanner;
}

function searchRecipe($query) {
    $app_id = "4577783c"; // Replace with actual App ID
    $app_key = "2ebd6b0aa43312e5f01f2077882ca32f"; // Replace with actual App Key
    $url = "https://api.edamam.com/api/recipes/v2?type=public&q=" . urlencode($query) . "&app_id={$app_id}&app_key={$app_key}";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    if (!$response) {
        return ["error" => "API request failed"];
    }
    curl_close($curl);

    $data = json_decode($response, true);
    if (!isset($data['hits'])) {
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
        case "getMealPlan":
            $dietaryRestrictions = $request['dietaryRestrictions'] ?? "";
            $caloriesPerMeal = $request['caloriesPerMeal'] ?? 500;
            return getWeeklyMealPlan($dietaryRestrictions, $caloriesPerMeal);

        case "searchRecipe":
            $query = $request['query'] ?? "";
            return searchRecipe($query);

        default:
            return ["error" => "Unsupported message type"];
    }
}

// Create a server that listens for requests from clients
$server = new rabbitMQServer("dmzConfig.ini", "dmzServer");
echo "DMZ Server for Meal Planning and Recipe Search is running and waiting for requests...\n";
$server->process_requests('requestProcessor');
?>
