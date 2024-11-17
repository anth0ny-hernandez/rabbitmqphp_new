<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: login.php");
//     exit();
// }

// // Refresh session token to extend expiration by another 90 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 90;
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

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightgrey;
            padding-top: 80px; /* To prevent overlap with the fixed navbar */
        }

        .container-custom {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 50px lightgreen;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .meal-input {
            margin-bottom: 15px;
        }

        .meal-type {
            display: inline-block;
            width: 100px;
            font-weight: bold;
        }

        input[type="text"] {
            width: calc(100% - 120px);
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .submit-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 18px;
            color: white;
            background-color: blue;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-button:hover {
            background-color: darkblue;
        }

        .navbar-brand {
            color: lightgreen !important;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .nav-link {
            color: white !important;
        }

        .nav-link:hover {
            color: green !important;
        }

        .dropdown-menu a {
            color: black !important;
        }

        .dropdown-menu a:hover {
            background-color: green !important;
            color: white !important;
        }

        .logout-button {
            background-color: crimson !important;
            color: white !important;
            border-radius: 4px;
            padding: 5px 15px;
            font-size: 14px;
        }

        .logout-button:hover {
            background-color: darkred !important;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <!-- Logout Button -->
        <a class="btn logout-button" href="logout.php">Logout</a>

        <!-- Navbar Brand -->
        <a class="navbar-brand" href="#">ARAY</a>

        <!-- Navbar Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <!-- Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Features
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="home.php">Home</a>
                        <a class="dropdown-item" href="search.php">Recipe Search</a>
                        <a class="dropdown-item" href="dietrestrictions.php">Diet Restrictions</a>
                        <a class="dropdown-item" href="recommendations.php">Recommendations</a>
                        <a class="dropdown-item" href="review.php">Rate and Review</a>
                        <a class="dropdown-item" href="weeklyMealPlanner.php">Weekly Meal Planner</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container container-custom">
    <h1>Weekly Meal Planner</h1>
    <form id="mealPlannerForm" action="weeklyMealPlanner.php" method="POST">
        <div class="planner">
            <!-- Days of the Week -->
            <?php 
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($days as $day): ?>
                <div class="day-card mb-4" id="<?php echo strtolower($day); ?>">
                    <h3><?php echo $day; ?></h3>
                    
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
</div>

<!-- Bootstrap JS, Popper.js, and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
