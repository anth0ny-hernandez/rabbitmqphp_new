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
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 20px; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
        .meal-item { border: 1px solid #ddd; padding: 10px; margin-top: 10px; text-align: left; }
        .dropdown { width: 100px; margin-left: 10px; }
        .meal-plan-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .meal-plan-table th, .meal-plan-table td { border: 1px solid #ddd; padding: 8px; }
        .meal-plan-table th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<div class="container">
    <h2>Weekly Meal Planner</h2>
    <?php if (isset($message)) echo "<p>$message</p>"; ?>

    <!-- Display the Current Weekly Meal Plan -->
    <h3>Your Current Weekly Meal Plan</h3>
    <?php if (!empty($currentMealPlan)): ?>
        <table class="meal-plan-table">
            <tr>
                <th>Day</th>
                <th>Meal</th>
                <th>Recipe</th>
                <th>Calories</th>
            </tr>
            <?php foreach ($currentMealPlan as $meal): ?>
                <tr>
                    <td><?php echo htmlspecialchars($meal['day']); ?></td>
                    <td><?php echo htmlspecialchars($meal['meal_type']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($meal['url']); ?>" target="_blank"><?php echo htmlspecialchars($meal['recipe']); ?></a></td>
                    <td><?php echo round($meal['calories']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No meals planned yet. Add meals below to create your weekly plan.</p>
    <?php endif; ?>

    <!-- Form to Assign Recipes to Weekly Planner -->
    <form method="POST">
        <h3>Plan Your Week</h3>
        <?php foreach ($selected_recipes as $recipe): ?>
            <div class="meal-item">
                <strong><?php echo htmlspecialchars($recipe->label); ?></strong>
                <input type="hidden" name="weekly_plan[<?php echo htmlspecialchars($recipe->label); ?>][url]" value="<?php echo htmlspecialchars($recipe->url); ?>">
                <input type="hidden" name="weekly_plan[<?php echo htmlspecialchars($recipe->label); ?>][calories]" value="<?php echo htmlspecialchars($recipe->calories); ?>">

                <label for="day">Day:</label>
                <select name="weekly_plan[<?php echo htmlspecialchars($recipe->label); ?>][day]" class="dropdown">
                    <option value="">Select Day</option>
                    <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day): ?>
                        <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="meal_type">Meal:</label>
                <select name="weekly_plan[<?php echo htmlspecialchars($recipe->label); ?>][meal_type]" class="dropdown">
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
