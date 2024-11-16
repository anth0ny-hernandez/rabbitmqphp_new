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

$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Handle selected recipes from the search page
$selectedFoods = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['foods'])) {
    $selectedFoods = array_map('json_decode', $_POST['foods'], true);
}

// Save meal planner data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createmealplanner'])) {
    foreach ($selectedFoods as $food) {
        $saveRequest = [
            "type" => "saveWeeklyMealPlan",
            "session_token" => $_COOKIE['session_token'],
            "foodDetails" => $food,
            "day" => $_POST['day'][$food['label']],
            "meal_type" => $_POST['meal_type'][$food['label']]
        ];
        $client->send_request($saveRequest);
    }
    echo "<p style='color:green;'>Weekly meal plan updated successfully!</p>";
}

// Fetch the current weekly meal plan
$request = [
    "type" => "fetchWeeklyMealPlan",
    "session_token" => $_COOKIE['session_token']
];

$response = $client->send_request($request);
$currentMealPlan = $response['weeklyPlan'] ?? [];
$groupedRecipes = [];

// Group recipes by day and meal type
foreach ($currentMealPlan as $meal) {
    $day = $meal['day'];
    $mealType = $meal['meal_type'];
    $recipe = $meal['recipe'];
    $groupedRecipes[$day][$mealType][] = $recipe;
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
            background-color: #f9f9f9;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="reviews.php" class="button">Ratings and Reviews</a>
        <a href="autoshopper.php" class="button">AutoShopper</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <h2>Weekly Meal Planner</h2>

    <!-- Display selected recipes with dropdowns -->
    <?php if (!empty($selectedFoods)): ?>
        <form method="POST" action="weeklyMealPlanner.php">
            <?php foreach ($selectedFoods as $food): ?>
                <div>
                    <strong><?php echo htmlspecialchars($food['label']); ?></strong>
                    <select name="day[<?php echo htmlspecialchars($food['label']); ?>]">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                    <select name="meal_type[<?php echo htmlspecialchars($food['label']); ?>]">
                        <option value="Breakfast">Breakfast</option>
                        <option value="Lunch">Lunch</option>
                        <option value="Dinner">Dinner</option>
                    </select>
                </div>
            <?php endforeach; ?>
            <input type="submit" name="createmealplanner" value="Save Weekly Meal Plan">
        </form>
    <?php endif; ?>

    <!-- Display current meal plan -->
    <?php if (!empty($currentMealPlan)): ?>
        <h3>Current Weekly Meal Plan</h3>
        <table>
            <tr>
                <th>Day</th>
                <th>Breakfast</th>
                <th>Lunch</th>
                <th>Dinner</th>
            </tr>
            <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day): ?>
                <tr>
                    <td><?php echo $day; ?></td>
                    <?php foreach (['Breakfast', 'Lunch', 'Dinner'] as $mealType): ?>
                        <td>
                            <?php if (isset($groupedRecipes[$day][$mealType])): ?>
                                <?php echo implode("<br>", $groupedRecipes[$day][$mealType]); ?>
                            <?php else: ?>
                                No Recipe
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No weekly meal plan found. Start adding meals!</p>
    <?php endif; ?>
</div>

</body>
</html>
