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

$recipeRecommendResponse = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recommendRecipe'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Collect form data for recipe search
    $request = [
        "type" => "recommendRecipe",
        "label" => $_POST['label'] ?? null,
        "healthLabels" => $_POST['healthLabels'] ?? null,
        "cuisineType" => $_POST['cuisineType'] ?? null,
        "mealType" => $_POST['mealType'] ?? null,
        "ENERC_KCAL" => $_POST['ENERC_KCAL'] ?? null,
    ];

    $recipeRecommendResponse = $client->send_request($request);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recommendations</title>
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
    <h2>Recommended Foods for You</h2>
    <!-- Display Logic for Recipe Recommendations Results -->
    <?php if (isset($recipeRecommendResponse['error'])): ?>
        <p><?php echo htmlspecialchars($recipeRecommendResponse['error']); ?></p>
    <?php elseif (isset($recipeRecommendResponse['hits']) && !empty($recipeRecommendResponse['hits'])): ?>
        <h3>Search Results:</h3>
        <?php foreach ($recipeRecommendResponse['hits'] as $hit): ?>
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
        <p>No recipes found. Please try a different search term.</p>
    <?php endif; ?>
</div>

</body>
<footer>
<div class="container">
    <div class="nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="search.php" class="button">Meal Plan</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
    </div>
</footer>
</html>
