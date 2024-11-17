<?php
// // Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: login.php");
//     exit();
// }

// // Refresh session token to extend expiration by another 90 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 90;
// setcookie('session_token', $session_token, $expire_time, "/");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Page Styling */
        body {
            font-family: Arial, sans-serif;
            background: lightgrey;
            padding-top: 56px; /* For fixed navbar */
        }

        .container-custom {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid black;
            border-radius: 8px;
            box-shadow: 0px 0px 50px lightgreen;
            background: white;
            text-align: center;
        }

        h1 {
            color: black;
        }

        p {
            color: darkslategrey;
        }

        /* Navbar Styling */
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
            color: lightgreen !important;
        }

        .dropdown-menu a {
            color: black !important;
        }

        .dropdown-menu a:hover {
            background-color: green !important;
            color: white !important;
        }

        .logout-button {
            background-color: red !important;
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
            <a class="btn logout-button" href="logout.php">Logout</a>
            
            <a class="navbar-brand" href="#">ARAY</a>

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
                            <a class="dropdown-item" href="search.php">Recipe Search</a>
                            <a class="dropdown-item" href="dietrestrictions.php">Diet Restrictions</a>
                            <a class="dropdown-item" href="recommendations.php">Recommendations</a>
                            <a class="dropdown-item" href="review.php">Rate and Review</a>
                            <a class="dropdown-item" href="mealplannerform.php">Weekly Meal Planner Form</a>
                            <a class="dropdown-item" href="weeklyMealPlanner.php">Weekly Meal Planner</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container container-custom">
        <h1>Welcome to the Home Page!</h1>
        <p>You are logged in. Your session will be automatically refreshed every 90 seconds to keep you logged in.</p>
        <p>To see all that our site has to offer, go to the features section in the top right corner of the screen.
            You can use the drop bar to access the different parts of our webpage.
        </p>
    </div>

    <!-- JavaScript to handle automatic logout after session expiration -->
    <script>
        // setTimeout(function() {
        //     document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        //     window.location.href = 'login.php';
        // }, 90000); // 90 seconds
    </script>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
