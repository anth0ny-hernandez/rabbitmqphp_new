<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: login.php");
//     exit();
// }

// // Refresh session token to extend expiration by another 30 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 30;
// setcookie('session_token', $session_token, $expire_time, "/");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['weeklyMealPlanner'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    $response = $client->send_request($request);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Meal Planner</title>
    <style>
  /* Page styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background: lightgrey;
        }

        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid black;
            border-radius: 8px;
            box-shadow: 0px 0px 50px lightgreen;
            background: white;
        }

        h1 {
            color: black;
        }

        p {
            color: darkslategrey;
        }
        .button-group {
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            color: white;
            background-color: blue;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background-color: darkblue;
        }
        
        .logout-button {
            background-color: red;
        }

        .logout-button:hover {
            background-color: darkred;
        }

        .login-button {
            background-color: green;
        }

        .login-button:hover {
            background-color: darkgreen;
        }
        .form-section {
            margin-bottom: 20px;
            font-size: 18px;
        }

        select, input[type="text"], textarea {
            font-size: 20px;
        }

        label {
            font-size: 20px;
        }

        select, input[type="number"], textarea {
            font-size: 20px;
        }

        select, input[type="submit"], button {
            font-size: 20px;
        }

        span{
            font-size: 20px;
        }

    </style>
</head>
<body>

<h1>Weekly Meal Planner</h1>
<form id="mealPlannerForm" action="weeklyMealPlanner.php" method="POST">

    <div class="planner">
        <!-- Days of the Week -->
        <?php 
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($days as $day): ?>
            <div class="day-card" id="<?php echo strtolower($day); ?>">
                <h2><?php echo $day; ?></h2>
                
                <div class="meal-input">
                    <span class="meal-type">Breakfast</span>
                    <input type="text" name="meals[<?php echo strtolower($day); ?>][breakfast]" placeholder="Enter breakfast">
                </div>
                <div class="meal-input">
                    <span class="meal-type">Lunch</span>
                    <input type="text" name="meals[<?php echo strtolower($day); ?>][lunch]" placeholder="Enter lunch">
                </div>
                <div class="meal-input">
                    <span class="meal-type">Dinner</span>
                    <input type="text" name="meals[<?php echo strtolower($day); ?>][dinner]" placeholder="Enter dinner">
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="submit" class="submit-button">Save Weekly Meals</button>
</form>

    <script>
    function saveMeals(day) {
    const breakfast = document.getElementById(`breakfast-${day}`).value;
    const lunch = document.getElementById(`lunch-${day}`).value;
    const dinner = document.getElementById(`dinner-${day}`).value;

    // Create a form dynamically
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "weeklyMealPlanner.php";

    // Add hidden inputs for each meal
    const inputs = [
        {name: 'day', value: day},
        {name: 'breakfast', value: breakfast},
        {name: 'lunch', value: lunch},
        {name: 'dinner', value: dinner}
    ];

    inputs.forEach(inputData => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = inputData.name;
        input.value = inputData.value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>

<!-- JavaScript to handle automatic logout after session expiration -->
<!-- <script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 30000); // 30 seconds
</script> -->
</body>
<footer>
<div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
</footer>
</html>
