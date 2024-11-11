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
        .meal-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Recipe Search</h2>

    <form method="POST" action="meal_plan.php">
        <label for="label">Search for Recipes:</label>
        <input type="text" id="label" name="label" placeholder="e.g., pasta, salad" required>
        <br><br>
        <input type="submit" name="searchRecipe" value="Search">
    </form>

    <?php if (isset($recipeSearchResponse['error'])): ?>
        <p><?php echo htmlspecialchars($recipeSearchResponse['error']); ?></p>
    <?php elseif (isset($recipeSearchResponse['hits']) && !empty($recipeSearchResponse['hits'])): ?>
        <form method="POST" action="weekly_meal_planner.php">
            <h3>Search Results:</h3>
            <?php foreach ($recipeSearchResponse['hits'] as $hit): ?>
                <div class="meal-item">
                    <input type="checkbox" name="selected_recipes[]" value="<?php echo htmlspecialchars(json_encode($hit['recipe'])); ?>">
                    <strong><?php echo htmlspecialchars($hit['recipe']['label']); ?></strong><br>
                    <a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">View Recipe</a><br>
                    Calories: <?php echo round($hit['recipe']['calories']); ?><br>
                    <?php if (!empty($hit['recipe']['image'])): ?>
                        <img src="<?php echo htmlspecialchars($hit['recipe']['image']); ?>" alt="<?php echo htmlspecialchars($hit['recipe']['label']); ?>" width="100">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <br>
            <input type="submit" name="addToPlanner" value="Transfer to Weekly Planner" class="button">
        </form>
    <?php endif; ?>
</div>

</body>
</html>
