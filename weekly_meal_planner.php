<?php
session_start();
require_once('rabbitMQLib.inc');

// Redirect to login if no session token
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Store selected recipes in session if coming from recipe search page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addToPlanner']) && isset($_POST['selected_recipes'])) {
    $_SESSION['selected_recipes'] = array_map('json_decode', $_POST['selected_recipes']);
}

// Get selected recipes from session
$selected_recipes = $_SESSION['selected_recipes'] ?? [];

// Fetch the user's current weekly meal plan from the database
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$request = [
    "type" => "fetchWeeklyMealPlan",
    "session_token" => $_COOKIE['session_token']
];
$response = $client->send_request($request);
$currentMealPlan = $response['weeklyPlan'] ?? [];

// Process form submission to save the weekly meal plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['savePlan'])) {
    $weeklyPlan = $_POST['weekly_plan'];
    $saveRequest = [
        "type" => "saveWeeklyMealPlan",
        "session_token" => $_COOKIE['session_token'],
        "weeklyPlan" => $weeklyPlan
    ];
    
    $saveResponse = $client->send_request($saveRequest);
    $message = $saveResponse['success'] ? "Weekly meal plan saved successfully!" : "Failed to save meal plan.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Meal Planner</title>
    <style>
        /* Basic styling for the planner page to match the home page */
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
        .meal-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Weekly Meal Planner</h2>

    <!-- Navigation Buttons -->
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="reviews.php" class="button">Ratings and Reviews</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <!-- Display Meal Plan and Form -->
    <h3>Your Current Weekly Meal Plan</h3>
    <!-- Insert the meal plan table and form code here -->

</div>

</body>
</html>

