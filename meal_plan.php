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
        .nav-buttons {
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
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <h2>Recipe Search</h2>

    <!-- Recipe Search Form -->
    <form method="POST" action="meal_plan.php">
        <label for="label">Search for Recipes:</label>
        <input type="text" id="label" name="label" placeholder="e.g., pasta, salad" required>
        <br><br>

        <label for="healthLabels">Health Labels (optional):</label>
        <input type="text" id="healthLabels" name="healthLabels" placeholder="e.g., vegan, gluten-free">
        <br><br>

        <label for="cuisineType">Cuisine Type (optional):</label>
        <input type="text" id="cuisineType" name="cuisineType" placeholder="e.g., Italian, Indian">
        <br><br>

        <label for="mealType">Meal Type (optional):</label>
        <input type="text" id="mealType" name="mealType" placeholder="e.g., Breakfast, Dinner">
        <br><br>

        <label for="ENERC_KCAL">Calories (optional):</label>
        <input type="number" id="ENERC_KCAL" name="ENERC_KCAL" placeholder="Max Calories">
        <br><br>

        <input type="submit" name="searchRecipe" value="Search">
    </form>

    <!-- Display Logic for Recipe Search Results -->
    <?php if (isset($recipeSearchResponse['error'])): ?>
        <p><?php echo htmlspecialchars($recipeSearchResponse['error']); ?></p>
    <?php elseif (isset($recipeSearchResponse['hits']) && !empty($recipeSearchResponse['hits'])): ?>
        <h3>Search Results:</h3>
        <?php foreach ($recipeSearchResponse['hits'] as $hit): ?>
            <div class="meal-item">
                <strong><?php echo htmlspecialchars($hit['recipe']['label']); ?></strong><br>
                <a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">View Recipe</a><br>
                Calories: <?php echo round($hit['recipe']['calories']); ?><br>
                <?php if (!empty($hit['recipe']['image'])): ?>
                    <img src="<?php echo htmlspecialchars($hit['recipe']['image']); ?>" alt="<?php echo htmlspecialchars($hit['recipe']['label']); ?>" width="100">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        
    <?php endif; ?>
</div>

<!-- JavaScript to handle automatic logout after session expiration -->
<script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 90000); // 90 seconds
</script>

</body>
</html>
