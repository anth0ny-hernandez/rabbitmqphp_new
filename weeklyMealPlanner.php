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

// if ($_SERVER["REQUEST_METHOD"] == "POST") {

//     $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

//     // Build the request with meal data
//     $request = [
//         'type' => 'save_meals',
//         'session_token' => $session_token,
//         'meals' => $_POST['meals'] ?? []
//     ];

//     // Send the request and receive the response
//     $response = $client->send_request($request);

//     if ($response && isset($response['meals'])) {
//         $meals = $response['meals'];
//     } else {
//         echo "<p>Error: Unable to retrieve meal data.</p>";
//     }
// }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Meal Plan</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightgray;
            padding-top: 80px; /* Prevent overlap with the fixed navbar */
        }

        .container-custom {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 20px lightgreen;
        }

        h2, h3 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
        }

        .meal-item {
            border: 1px solid lightgrey;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            box-shadow: 0px 0px 50px lightgreen;
        }

        .logout-button {
            background-color: crimson;
            color: white;
            padding: 5px 15px;
            font-size: 14px;
            border-radius: 4px;
        }

        .logout-button:hover {
            background-color: darkred;
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

        /* Additional Styling */
        .meal-day-title {
            font-size: 24px;
            color: black;
            margin-top: 20px;
        }

        .meal-description {
            font-size: 18px;
            color: dimgray;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Logout Button -->
            <a class="btn logout-button" href="logout.php">Logout</a>

            <a class="navbar-brand" href="#">ARAY</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Features
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="home.php">Home</a>
                            <a class="dropdown-item" href="search.php">Recipe Search</a>
                            <a class="dropdown-item" href="dietrestrictions.php">Diet Restrictions</a>
                            <a class="dropdown-item" href="recommendations.php">Recommendations</a>
                            <a class="dropdown-item" href="mealplannerform.php">Weekly Meal Planner Form</a>
                            <a class="dropdown-item" href="weeklyMealPlanner.php">Weekly Meal Planner</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Meal Plan Content -->
    <div class="container container-custom">
        <h2>Your Weekly Meal Plan</h2>

        <!-- Meal Plan for Each Day -->
        <?php if (!empty($meals)): ?>
            <?php foreach ($meals as $day => $mealData): ?>
                <div class="meal-item">
                    <h3 class="meal-day-title"><?php echo ucfirst($day); ?></h3>
                    <div class="meal-description"><strong>Breakfast:</strong> <?php echo htmlspecialchars($mealData['breakfast'] ?? ''); ?></div>
                    <div class="meal-description"><strong>Lunch:</strong> <?php echo htmlspecialchars($mealData['lunch'] ?? ''); ?></div>
                    <div class="meal-description"><strong>Dinner:</strong> <?php echo htmlspecialchars($mealData['dinner'] ?? ''); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No meals were submitted.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
