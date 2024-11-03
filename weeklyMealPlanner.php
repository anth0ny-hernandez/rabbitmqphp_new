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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Build the request with meal data
    $request = [
        'type' => 'save_meals',
        'session_token' => $session_token,
        'meals' => $_POST['meals'] ?? []
    ];

    // Send the request and receive the response
    $response = $client->send_request($request);

    if ($response && isset($response['meals'])) {
        $meals = $response['meals'];
    } else {
        echo "<p>Error: Unable to retrieve meal data.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Meal Plan</title>
    <style>
        /* Simple Styling */
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

        p {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Weekly Meal Plan</h1>
        <?php if (!empty($meals)): ?>
            <?php foreach ($meals as $day => $mealData): ?>
                <h2><?php echo ucfirst($day); ?></h2>
                <p><strong>Breakfast:</strong> <?php echo htmlspecialchars($mealData['breakfast'] ?? ''); ?></p>
                <p><strong>Lunch:</strong> <?php echo htmlspecialchars($mealData['lunch'] ?? ''); ?></p>
                <p><strong>Dinner:</strong> <?php echo htmlspecialchars($mealData['dinner'] ?? ''); ?></p>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No meals were submitted.</p>
        <?php endif; ?>
    </div>

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
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="mealplannerform.php" class="button">Weekly Meal Planner Form</Form></a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
</footer>
</html>
