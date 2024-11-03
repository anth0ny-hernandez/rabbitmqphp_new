<?php
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
</head>
<body>

<div class="container">
    <h1>Welcome to the Home Page!</h1>
    <p>You are logged in. Your session will be automatically refreshed every 30 seconds to keep you logged in.</p>

    <div class="button-group">
        <a href="meal_plan.php" class="button">Go to Meal Planner</a>
        <a href="recipe_search.php" class="button">Search for Recipes</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
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
