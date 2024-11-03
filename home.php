<?php
// // Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: index.php");
//     exit();
// }

// // Refresh session token to extend expiration by another 30 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 30;
// setcookie('session_token', $session_token, $expire_time, "/");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
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
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome to the Home Page!</h1>
    <p>Please select what you would like to do today. You will be logged out in 30 seconds.</p>

    <div class="button-group">
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="mealplannerform.php" class="button">Weekly Meal Planner Form</Form></a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
</div>

<!-- JavaScript to handle automatic logout after session expiration -->
<!-- <script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 30000); // 30 seconds
</script> -->

</body>
</html>
