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

// Initialize variables to store current restrictions
$dietRestrictions = "";
$responseMessage = "";

// Check if dietary restrictions are already saved
// $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
// $request = [
//     "type" => "getDietRestrictions",
//     "session_token" => $session_token
// ];
// $response = $client->send_request($request);

if ($response['success']) {
    // Populate the form fields with existing data
    $dietRestrictions = $response['dietRestrictions'];
} else {
    $responseMessage = "No dietary restrictions saved yet.";
}

// Handle form submission to save new restrictions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setRestrictions'])) {
    $dietType = isset($_POST['dietaryRestrictions']) ? implode(", ", $_POST['dietaryRestrictions']) : "";
    $dietRestrictions = htmlspecialchars($_POST['dietRestrictions'] ?? "");

    // Prepare and send request to save dietary restrictions
    $request = [
        "type" => "dietRestrictions",
        "session_token" => $session_token,
        "dietRestrictions" => $dietRestrictions
    ];

    $response = $client->send_request($request);
    $responseMessage = $response['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Dietary Restrictions</title>
    <style>
        /* Page styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
            background-color: lightgrey;
        }

        li {
            
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
        .response-container {
            margin-top: 20px;
            font-size: 18px;
            background-color: #f3f4f6;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .logout-button {
            background-color: red;
        }

        .logout-button:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <h2>Set Your Dietary Restrictions and Concerns</h2>
    <h2>Examples:</h2>
    <li>vegetarian</li>
    <li>pescatarian</li>
    <li>pork-free</li>
    <li>alcohol-free</li>
    <br>

    <!-- Dietary Restrictions Form -->
    <form method="POST" action="">
        <!-- Diet Restrictions Section -->
        <div class="form-section">
            <label for="dietRestrictions">Diet Restrictions (optional):</label><br>
            <input type="text" id="dietRestrictions" name="dietRestrictions" placeholder="e.g., low sodium, low sugar" value="<?php echo htmlspecialchars($dietRestrictions); ?>">
        </div>

        <input type="submit" name="setRestrictions" value="Save Restrictions" class="button">
    </form>

    <?php if (!empty($responseMessage)): ?>
        <div class="response-container">
            <p><?php echo $responseMessage; ?></p>
        </div>
    <?php endif; ?>

<script>
// setTimeout(function() {
//     document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
//     window.location.href = 'login.php';
// }, 90000); // 90 seconds
</script>

</body>
<footer>
<div class="container">
        <a href="home.php" class="button">Home</a>
        <a href="search_recipe.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="mealplannerform.php" class="button">Weekly Meal Planner Form</a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
</footer>
</html>
