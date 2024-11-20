<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 90 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");

// Fetch saved recipes from the weekly meal planner
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$request = [
    "type" => "fetchIngredients",
    "session_token" => $session_token
];
$response = $client->send_request($request);

$recipes = $response['recipes'] ?? [];
$ingredientsList = [];

// Fetch ingredients for each recipe using the Edamam API
foreach ($recipes as $recipeLabel) {
    $apiRequest = [
        "type" => "searchRecipe",
        "label" => $recipeLabel
    ];
    $apiResponse = $client->send_request($apiRequest);

    if (isset($apiResponse['hits']) && !empty($apiResponse['hits'])) {
        $recipeData = $apiResponse['hits'][0]['recipe'];
        $ingredientsList[$recipeLabel] = $recipeData['ingredientLines'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AutoShopper</title>
    <style>
        /* Basic styling for the autoshopper page */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        .button-group {
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .logout-button {
            background-color: #dc3545;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        .recipe-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        .recipe-card h3 {
            color: #007bff;
            margin: 0;
        }
        .recipe-card ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>AutoShopper</h2>

    <!-- Navigation Buttons -->
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="autoshopper.php" class="button">AutoShopper</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <!-- Display Ingredients -->
    <?php if (!empty($ingredientsList)): ?>
        <?php foreach ($ingredientsList as $recipeLabel => $ingredients): ?>
            <div class="recipe-card">
                <h3><?php echo htmlspecialchars($recipeLabel); ?></h3>
                <ul>
                    <?php foreach ($ingredients as $ingredient): ?>
                        <li><?php echo htmlspecialchars($ingredient); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No recipes saved in the weekly meal planner.</p>
    <?php endif; ?>
</div>

</body>
</html>
