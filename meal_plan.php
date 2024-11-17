<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 30 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");

$recipeSearchResponse = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searchRecipe'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Collect form data for recipe search
    $request = [
        "type" => "searchRecipe",
        "label" => $_POST['label'] ?? null,
        "healthLabels" => $_POST['healthLabels'] ?? null,
        "cuisineType" => $_POST['cuisineType'] ?? null,
        "mealType" => $_POST['mealType'] ?? null,
        "ENERC_KCAL" => $_POST['ENERC_KCAL'] ?? null,
    ];

    $recipeSearchResponse = $client->send_request($request);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recipe Search</title>
    <style>
        /* Basic styling for the recipe search page to match the home page */
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
            cursor: pointer.
        }
        .button:hover {
            background-color: #0056b3.
        }
        .logout-button {
            background-color: #dc3545.
        }
        .logout-button:hover {
            background-color: #c82333.
        }
        .meal-item {
            border: 1px solid #ddd.
            border-radius: 8px.
            padding: 15px.
            margin-bottom: 10px.
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1).
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Recipe Search</h2>

    <!-- Navigation Buttons -->
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="reviews.php" class="button">Ratings and Reviews</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <!-- Recipe Search Form -->
    <form method="POST" action="meal_plan.php">
        <label for="label">Search for Recipes:</label>
        <input type="text" id="label" name="label" placeholder="e.g., pasta, salad" required>
        <br><br>
        <input type="submit" name="searchRecipe" value="Search">
    </form>
</div>

</body>
</html>

