<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('rabbitMQLib.inc');

// Redirect to login if no session token
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$selected_recipes = [];
$currentMealPlan = [];
$message = "";

// Check if the user is submitting selected recipes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_recipes'])) {
    if (is_array($_POST['selected_recipes'])) {
        $selected_recipes = array_map('json_decode', $_POST['selected_recipes']);
    } else {
        error_log("selected_recipes is not an array: " . print_r($_POST['selected_recipes'], true));
    }
}

// Fetch the user's current weekly meal plan from the database
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$mealPlanRequest = [
    "type" => "fetchWeeklyMealPlan",
    "session_token" => $_COOKIE['session_token']
];
$mealPlanResponse = $client->send_request($mealPlanRequest);
$currentMealPlan = $mealPlanResponse['weeklyPlan'] ?? [];

// Debug: Log the fetched meal plan
error_log("Current Meal Plan: " . print_r($currentMealPlan, true));

// Process form submission to save new additions to the weekly meal plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['savePlan'])) {
    $weeklyPlan = $_POST['weekly_plan'];
    $saveRequest = [
        "type" => "updateWeeklyMealPlan",
        "session_token" => $_COOKIE['session_token'],
        "weeklyPlan" => $weeklyPlan
    ];

    $saveResponse = $client->send_request($saveRequest);
    $message = $saveResponse['success'] ? "Weekly meal plan updated successfully!" : "Failed to update meal plan.";

    // Debug: Log the save response
    error_log("Save Response: " . print_r($saveResponse, true));
}

// Process request to remove a meal from the plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeMeal'])) {
    $removeRequest = [
        "type" => "removeMealFromPlan",
        "session_token" => $_COOKIE['session_token'],
        "recipe" => $_POST['recipe'],
        "day" => $_POST['day'],
        "meal_type" => $_POST['meal_type']
    ];
    $removeResponse = $client->send_request($removeRequest);
    $message = $removeResponse['success'] ? "Meal removed successfully!" : "Failed to remove meal.";

    // Debug: Log the remove response
    error_log("Remove Response: " . print_r($removeResponse, true));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Meal Planner</title>
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
        .meal-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        .meal-plan-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .meal-plan-table th, .meal-plan-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .meal-plan-table th {
            background-color: #f2f2f2;
        }
        .remove-button {
            color: red;
            cursor: pointer;
            font-size: 0.8em;
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
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <h2>Weekly Meal Planner</h2>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- Display Weekly Meal Plan -->
    <h3>Your Current Weekly Meal Plan</h3>
    <?php if (!empty($currentMealPlan)): ?>
        <table class="meal-plan-table">
            <tr>
                <th>Day</th>
                <th>Meals</th>
            </tr>
            <?php foreach ($currentMealPlan as $day => $meals): ?>
                <tr>
                    <td><?php echo htmlspecialchars($day); ?></td>
                    <td><?php echo htmlspecialchars(implode(", ", array_map(function($meal) {
                        return $meal['recipe'];
                    }, $meals))); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No meals planned yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
