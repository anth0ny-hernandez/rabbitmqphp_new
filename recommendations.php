<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 30 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");

// Send a request to get recipe recommendations
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$request = [
    "type" => "recommendRecipes",
    "session_token" => $session_token
];
$response = $client->send_request($request);

// Check if there was an error in the response
if (isset($response['error'])) {
    $error_message = $response['error'];
} else {
    $recipes = $response['hits'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Recommendations</title>

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

        h2 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
        }

        .recipe-card {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid black;
        }

        .recipe-card img {
            width: 100%;
            border-radius: 8px;
        }

        .recipe-card h3 {
            font-size: 24px;
            color: darkslategray;
        }

        .recipe-card p {
            font-size: 16px;
        }

        .btn-primary {
            background-color: blue;
            border: none;
        }

        .btn-primary:hover {
            background-color: darkblue;
        }

        .error-message {
            color: crimson;
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

    <div class="container container-custom">
        <h2>Recipe Recommendations</h2>

        <!-- Error Message -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (isset($recipes) && !empty($recipes)): ?>
            <!-- Loop through recipes -->
            <?php foreach ($recipes as $hit): ?>
                <div class="recipe-card">
                    <h3><?php echo htmlspecialchars($hit['recipe']['label']); ?></h3>
                    <a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($hit['recipe']['image']); ?>" alt="<?php echo htmlspecialchars($hit['recipe']['label']); ?>">
                    </a>
                    <p><strong>Calories:</strong> <?php echo round($hit['recipe']['calories']); ?></p>
                    <p><a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank" class="btn btn-primary">View Recipe</a></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">No recipes found. Please try again later.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
