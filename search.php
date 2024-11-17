<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: login.php");
//     exit();
// }

// Refresh session token to extend expiration by another 90 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 90;
// setcookie('session_token', $session_token, $expire_time, "/");

$recipeSearchResponse = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searchRecipe'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Collect form data for recipe search
    $request = [
        "type" => "searchRecipe",
        "label" => $_POST['label'] ?? null,
        "healthLabels" => $_POST['healthLabels'] ?? null,
        "cuisineType" => $_POST['cuisineType'] ?? null,
        "mealType" => $_POST['mealType'] ?? null,
        "ENERC_KCAL" => $_POST['ENERC_KCAL'] ?? null,
    ];

    $recipeSearchResponse = $client->send_request($request);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Search</title>

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

        .form-section {
            margin-bottom: 20px;
            font-size: 18px;
        }

        input[type="text"], input[type="number"], textarea {
            font-size: 20px;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid black;
            border-radius: 4px;
            width: 100%;
        }

        input[type="submit"] {
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

        input[type="submit"]:hover {
            background-color: darkblue;
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
        <h2>Recipe Search</h2>

        <!-- Recipe Search Form -->
        <form method="POST" action="search_recipe.php">
            <div class="form-section">
                <label for="label">Search for Recipes:</label>
                <input type="text" id="label" name="label" placeholder="e.g., pasta, salad" required>
            </div>

            <div class="form-section">
                <label for="healthLabels">Health Labels (optional):</label>
                <input type="text" id="healthLabels" name="healthLabels" placeholder="e.g., vegan, gluten-free">
            </div>

            <div class="form-section">
                <label for="cuisineType">Cuisine Type (optional):</label>
                <input type="text" id="cuisineType" name="cuisineType" placeholder="e.g., Italian, Indian">
            </div>

            <div class="form-section">
                <label for="mealType">Meal Type (optional):</label>
                <input type="text" id="mealType" name="mealType" placeholder="e.g., Breakfast, Dinner">
            </div>

            <div class="form-section">
                <label for="ENERC_KCAL">Calories (optional):</label>
                <input type="number" id="ENERC_KCAL" name="ENERC_KCAL" placeholder="Max Calories">
            </div>

            <div class="form-section">
                <input type="submit" name="searchRecipe" value="Search">
            </div>
        </form>

        <!-- Display Logic for Recipe Search Results -->
        <?php if (isset($recipeSearchResponse['error'])): ?>
            <p><?php echo htmlspecialchars($recipeSearchResponse['error']); ?></p>
        <?php elseif (isset($recipeSearchResponse['hits']) && !empty($recipeSearchResponse['hits'])): ?>
            <h3>Search Results:</h3>
            <?php foreach ($recipeSearchResponse['hits'] as $hit): ?>
                <div class="meal-item">
                    <strong><?php echo htmlspecialchars($hit['recipe']['label']); ?></strong><br>
                    <a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">View Recipe</a><br>
                    Calories: <?php echo round($hit['recipe']['calories']); ?><br>
                    <?php if (!empty($hit['recipe']['image'])): ?>
                        <img src="<?php echo htmlspecialchars($hit['recipe']['image']); ?>" alt="<?php echo htmlspecialchars($hit['recipe']['label']); ?>" width="100">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results found. Try adjusting your search criteria.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
