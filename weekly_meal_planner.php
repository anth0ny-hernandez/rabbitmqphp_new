<?php
require_once('rabbitMQLib.inc');

// Redirect to login if no session token
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Fetch selected recipes from POST data
$selected_recipes = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_recipes'])) {
    $selected_recipes = array_map('json_decode', $_POST['selected_recipes'], true);
}

// Fetch the user's current weekly meal plan from the database
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$request = [
    "type" => "fetchWeeklyMealPlan",
    "session_token" => $_COOKIE['session_token']
];
$response = $client->send_request($request);
$currentMealPlan = $response['weeklyPlan'] ?? [];

// Process form submission to save new additions to the weekly meal plan
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['savePlan'])) {
    $weeklyPlan = $_POST['weekly_plan'];
    $saveRequest = [
        "type" => "updateWeeklyMealPlan",
        "session_token" => $_COOKIE['session_token'],
        "weeklyPlan" => $weeklyPlan
    ];

    $saveResponse = $client->send_request($saveRequest);
    $message = $saveResponse['success'] ? "Weekly meal plan updated successfully!" : "Failed to update meal plan.";
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

    <!-- Display the Current Weekly Meal Plan -->
    <h3>Your Current Weekly Meal Plan</h3>
    <?php if (!empty($currentMealPlan)): ?>
        <table class="meal-plan-table">
            <tr>
                <th>Day</th>
                <th>Meals</th>
            </tr>
            <?php
            // Organize meals by day
            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($daysOfWeek as $day) {
                echo "<tr><td><strong>$day</strong></td><td>";
                foreach ($currentMealPlan as $meal) {
                    if ($meal['day'] === $day) {
                        echo "<div><strong>{$meal['meal_type']}</strong>: ";
                        echo "<a href='{$meal['url']}' target='_blank'>{$meal['recipe']}</a>";
                        echo " ({$meal['calories']} calories)";
                        echo "<form method='POST' style='display:inline;'>
                                <input type='hidden' name='recipe' value='{$meal['recipe']}'>
                                <input type='hidden' name='day' value='$day'>
                                <input type='hidden' name='meal_type' value='{$meal['meal_type']}'>
                                <button type='submit' name='removeMeal' class='remove-button'>Remove</button>
                              </form>";
                        echo "</div>";
                    }
                }
                echo "</td></tr>";
            }
            ?>
        </table>
    <?php else: ?>
        <p>No meals planned yet. Add meals below to create your weekly plan.</p>
    <?php endif; ?>

    <!-- Form to Assign Recipes to Weekly Planner -->
    <form method="POST">
        <h3>Plan Your Week</h3>
        <?php foreach ($selected_recipes as $recipe): ?>
            <div class="meal-item">
                <strong><?php echo htmlspecialchars($recipe['label']); ?></strong>
                <input type="hidden" name="weekly_plan[<?php echo htmlspecialchars($recipe['label']); ?>][url]" value="<?php echo htmlspecialchars($recipe['url']); ?>">
                <input type="hidden" name="weekly_plan[<?php echo htmlspecialchars($recipe['label']); ?>][calories]" value="<?php echo htmlspecialchars($recipe['calories']); ?>">

                <label for="day">Day:</label>
                <select name="weekly_plan[<?php echo htmlspecialchars($recipe['label']); ?>][day]" class="dropdown">
                    <option value="">Select Day</option>
                    <?php foreach ($daysOfWeek as $day): ?>
                        <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="meal_type">Meal:</label>
                <select name="weekly_plan[<?php echo htmlspecialchars($recipe['label']); ?>][meal_type]" class="dropdown">
                    <option value="">Select Meal</option>
                    <option value="Breakfast">Breakfast</option>
                    <option value="Lunch">Lunch</option>
                    <option value="Dinner">Dinner</option>
                </select>
            </div>
        <?php endforeach; ?>

        <br>
        <input type="submit" name="savePlan" value="Save Weekly Plan" class="button">
    </form>
</div>

</body>
</html>
