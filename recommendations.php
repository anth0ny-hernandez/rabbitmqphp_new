<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 30 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 30;
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
    <title>Recipe Recommendations</title>
    <style>
        /* Basic styling for the recommendations page to match the home page */
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
        h2 {
            color: #333;
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
        .recipe-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
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
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Recipe Recommendations</h2>

    <!-- Navigation Buttons -->
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
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

<!-- JavaScript to handle automatic logout after session expiration -->
<script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 30000); // 30 seconds
</script>

</body>
</html>
