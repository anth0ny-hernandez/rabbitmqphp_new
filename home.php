<?php
require_once('rabbitMQLib.inc');
// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Fetch random meal from testRabbitMQServer
// $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
// $request = ["type" => "getRandomMeal"];
// $response = $client->send_request($request);
// $randomMeal = $response['success'] ? $response : null;

// Refresh session token to extend expiration by another 30 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
    <style>
        /* Basic styling for the home page */
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
        h1 {
            color: #333;
        }
        p {
            color: #666;
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
            cursor: pointer;
        }
        .notification {
            display: none;
            background-color: #007bff;
            color: white;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            position: relative;
            animation: fadeout 10s forwards;
        }
        @keyframes fadeout {
            0% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; display: none; }
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
    </style>
    <script>
        // Function to display the notification
        function showNotification() {
            const notification = document.getElementById("notification");
            notification.style.display = "block";
        }

        // Trigger notification on page load
        window.onload = showNotification;
    </script>
</head>
<body>

<div class="container">
    <h1>Welcome to the Home Page!</h1>
    <p>You are logged in. Your session will be automatically refreshed every 30 seconds to keep you logged in.</p>

    <div class="button-group">
        <a href="home.php" class="button">Home Page</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner </a>
        <a href="autoshopper.php" class="button">Autoshopper </a>
        <a href="calorieTracker.php" class="button">Calorie Tracker</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
</div>

<!-- Random Meal Notification -->
<?php if ($randomMeal): ?>
        <div id="notification" class="notification" onclick="window.location.href='<?php echo htmlspecialchars($randomMeal['url']); ?>'">
            🎉 <strong>Meal of the Day:</strong> <?php echo htmlspecialchars($randomMeal['recipe']); ?> (Click here for details!)
        </div>
    <?php else: ?>
        <div>No meal available at the moment.</div>
    <?php endif; ?>
    
<!-- JavaScript to handle automatic logout after session expiration -->
<script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 90000); // 30 seconds
</script>

</body>
</html>
