<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    // If not set, redirect to login page
    header("Location: login.php");
    exit();
}

// Get the session token from the cookie
$session_token = $_COOKIE['session_token'];

// Refresh the cookie to extend the expiration by another 30 seconds
$expire_time = time() + 30;
setcookie('session_token', $session_token, $expire_time, "/");

$mealPlanResponse = null;
$recipeSearchResponse = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    if (isset($_POST['searchRecipe'])) {
        // Recipe search request
        $query = $_POST['recipeQuery'];
        $request = [
            "type" => "searchRecipe",
            "query" => $query
        ];
        $recipeSearchResponse = $client->send_request($request);
    } elseif (isset($_POST['generateMealPlan'])) {
        // Weekly meal planner request
        $dietaryRestrictions = $_POST['dietaryRestrictions'] ?? "";
        $caloriesPerMeal = $_POST['caloriesPerMeal'] ?? 500;

        $request = [
            "type" => "getMealPlan",
            "dietaryRestrictions" => $dietaryRestrictions,
            "caloriesPerMeal" => $caloriesPerMeal
        ];
        $mealPlanResponse = $client->send_request($request);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Meal Planner & Recipe Search</title>
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
        .meal-day, .meal-item {
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
    <div class="nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Meal Planner</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <h2>Weekly Meal Planner</h2>

    <!-- Meal Plan Form -->
    <form method="POST" action="meal_plan.php">
        <label for="dietaryRestrictions">Dietary Restrictions (optional):</label>
        <input type="text" id="dietaryRestrictions" name="dietaryRestrictions" placeholder="e.g., vegan, gluten-free">
        <br><br>
        <label for="caloriesPerMeal">Calories per Meal (optional):</label>
        <input type="number" id="caloriesPerMeal" name="caloriesPerMeal" value="500">
        <br><br>
        <input type="submit" name="generateMealPlan" value="Generate Meal Plan">
    </form>

    <?php if ($mealPlanResponse): ?>
        <h3>Your Weekly Meal Plan:</h3>
        <?php foreach ($mealPlanResponse as $day => $meals): ?>
            <div class="meal-day">
                <h3><?php echo $day; ?></h3>
                <?php foreach ($meals as $mealName => $mealData): ?>
                    <div class="meal-item">
                        <strong><?php echo $mealName; ?>:</strong>
                        <p>Meal: <?php echo $mealData['label']; ?></p>
                        <p>Calories: <?php echo round($mealData['calories']); ?></p>
                        <a href="<?php echo $mealData['url']; ?>" target="_blank">View Recipe</a><br>
                        <?php if (!empty($mealData['image'])): ?>
                            <img src="<?php echo $mealData['image']; ?>" alt="<?php echo $mealData['label']; ?>" width="100">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php elseif ($mealPlanResponse !== null): ?>
        <p>Could not retrieve meal plan. Please try again later.</p>
    <?php endif; ?>

    <h2>Recipe Search</h2>

    <!-- Recipe Search Form -->
    <form method="POST" action="meal_plan.php">
        <label for="recipeQuery">Search for Recipes:</label>
        <input type="text" id="recipeQuery" name="recipeQuery" placeholder="e.g., pasta, salad" required>
        <br><br>
        <input type="submit" name="searchRecipe" value="Search">
    </form>

    <?php if ($recipeSearchResponse && isset($recipeSearchResponse['hits'])): ?>
        <h3>Search Results:</h3>
        <?php foreach ($recipeSearchResponse['hits'] as $hit): ?>
            <div class="meal-item">
                <strong><?php echo $hit['recipe']['label']; ?></strong><br>
                <a href="<?php echo $hit['recipe']['url']; ?>" target="_blank">View Recipe</a><br>
                Calories: <?php echo round($hit['recipe']['calories']); ?><br>
                <?php if (!empty($hit['recipe']['image'])): ?>
                    <img src="<?php echo $hit['recipe']['image']; ?>" alt="<?php echo $hit['recipe']['label']; ?>" width="100">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php elseif ($recipeSearchResponse !== null): ?>
        <p>No recipes found. Please try a different search term.</p>
    <?php endif; ?>
</div>

<!-- JavaScript to handle automatic logout after session expiration -->
<script>
    setTimeout(function() {
        // Delete the session token cookie by setting its expiration date in the past
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        // Redirect to the login page
        window.location.href = 'login.php';
    }, 30000); // 30,000 milliseconds = 30 seconds
</script>

</body>
</html>
