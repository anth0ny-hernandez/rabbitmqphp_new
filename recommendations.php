<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Get the session token from the cookie
$session_token = $_COOKIE['session_token'];

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
    <title>Recipe Recommendations</title>
    <style>
        /* Basic styling for the recommendations page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #333;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        .recipe-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .recipe-card img {
            max-width: 100%;
            border-radius: 8px;
        }
        .recipe-card h3 {
            color: #007bff;
            margin: 0;
        }
        .recipe-card p {
            color: #555;
        }
        .recipe-card a {
            color: #007bff;
            text-decoration: none;
        }
        .recipe-card a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Recipe Recommendations</h2>

    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <?php if (isset($error_message)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php elseif (isset($recipes) && !empty($recipes)): ?>
        <?php foreach ($recipes as $hit): ?>
            <div class="recipe-card">
                <h3><?php echo htmlspecialchars($hit['recipe']['label']); ?></h3>
                <a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($hit['recipe']['image']); ?>" alt="<?php echo htmlspecialchars($hit['recipe']['label']); ?>">
                </a>
                <p><strong>Calories:</strong> <?php echo round($hit['recipe']['calories']); ?></p>
                <p><a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">View Recipe</a></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="error-message">No recipes found. Please try again later.</p>
    <?php endif; ?>
</div>

</body>
</html>
