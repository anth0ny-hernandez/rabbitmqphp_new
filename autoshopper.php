<?php
session_start();
require_once('rabbitMQLib.inc');

// Redirect to login if no session token
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Set up RabbitMQ client to fetch weekly meal plan
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$mealPlanRequest = [
    "type" => "fetchWeeklyMealPlan",
    "session_token" => $_COOKIE['session_token']
];
$mealPlanResponse = $client->send_request($mealPlanRequest);
$savedRecipes = $mealPlanResponse['weeklyPlan'] ?? [];

// Set up an array to store ingredients for each recipe
$ingredientsList = [];

// Fetch ingredients for each recipe in the saved weekly meal plan
foreach ($savedRecipes as $meal) {
    $dmzClient = new rabbitMQClient("dmzConfig.ini", "dmzServer");
    $dmzRequest = [
        "type" => "searchRecipe",
        "label" => $meal['recipe'] // Use the recipe name from saved weekly meal plan
    ];
    $dmzResponse = $dmzClient->send_request($dmzRequest);

    // Check and add ingredients if the response is successful
    if (isset($dmzResponse['hits'][0])) {
        $ingredientsList[$meal['recipe']] = $dmzResponse['hits'][0]['recipe']['ingredientLines'];
    } else {
        $ingredientsList[$meal['recipe']] = ["Ingredients not found"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AutoShopper</title>
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .button-group {
            margin-bottom: 20px;
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
        .ingredient-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="reviews.php" class="button">Ratings and Reviews</a>
        <a href="weekly_meal_planner.php" class="button">Weekly Meal Planner</a>
        <a href="autoshopper.php" class="button">AutoShopper</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <h2>AutoShopper - Ingredients for Your Weekly Plan</h2>

    <?php if (!empty($ingredientsList)): ?>
        <?php foreach ($ingredientsList as $recipe => $ingredients): ?>
            <div class="ingredient-item">
                <h3><?php echo htmlspecialchars($recipe); ?></h3>
                <ul>
                    <?php foreach ($ingredients as $ingredient): ?>
                        <li><?php echo htmlspecialchars($ingredient); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No recipes found in your weekly meal plan. Please add recipes to view ingredients.</p>
    <?php endif; ?>
</div>

</body>
</html>
