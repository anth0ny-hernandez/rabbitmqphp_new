<?php
require_once('rabbitMQLib.inc');

// // Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: login.php");
//     exit();
// }

// // Refresh session token to extend expiration by another 90 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 90;
// setcookie('session_token', $session_token, $expire_time, "/");

// // Send a request to get recipe recommendations
// $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
// $request = [
//     "type" => "recommendRecipes",
//     "session_token" => $session_token
// ];
// $response = $client->send_request($request);

// // Check if there was an error in the response
// if (isset($response['error'])) {
//     $error_message = $response['error'];
// } else {
//     $recipes = $response['hits'];
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recipe Recommendations</title>
    <style>
    /* Page styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
            background-color: lightgrey;
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
            color: white;
            background-color: blue;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background-color: darkblue;
        }

        h2 {
            margin-top: 0;
        }

        .form-section {
            margin-bottom: 20px;
            font-size: 18px;
        }

        select, input[type="text"], textarea {
            font-size: 20px;
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
            background-color: white;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .highlight {
            font-weight: bold;
        }
        
        li{
            font-size: 20px;
        }

        p{
            font-size: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Recipe Recommendations</h2>
    <?php if (isset($error_message)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php elseif (isset($recipes) && !empty($recipes)): ?>
        <?php foreach ($recipes as $hit): ?>
            <div class="recipe-card">
                <h3><?php echo htmlspecialchars($hit['recipe']['label']); ?></h3>
                <a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($hit['recipe']['image']); ?>" alt="<?php echo htmlspecialchars($hit['recipe']['label']); ?>">
                </a>
                <p><strong>Calories:</strong> <?php echo round($hit['recipe']['calories']); ?></p>
                <p><a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">View Recipe</a></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="error-message">No recipes found. Please try again later.</p>
    <?php endif; ?>
</div>

<!-- JavaScript to handle automatic logout after session expiration -->
<!-- <script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 90000); // 90 seconds
</script> -->

</body>
<footer>
<div class="container">
    <div class="nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="mealplannerform.php" class="button">Weekly Meal Planner Form</Form></a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
    </div>
</footer>
</html>
