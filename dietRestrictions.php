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

// Initialize variables to store current restrictions
$dietaryRestrictions = [];
$otherRestrictions = "";
$responseMessage = "";

// Check if dietary restrictions are already saved
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$request = [
    "type" => "getDietRestrictions",
    "session_token" => $session_token
];
$response = $client->send_request($request);

if ($response['success']) {
    // Populate the form fields with existing data
    $dietaryRestrictions = explode(", ", $response['dietaryRestrictions']);
    $otherRestrictions = $response['otherRestrictions'];
} else {
    $responseMessage = "No dietary restrictions saved yet.";
}

// Handle form submission to save new restrictions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setRestrictions'])) {
    $dietType = isset($_POST['dietaryRestrictions']) ? implode(", ", $_POST['dietaryRestrictions']) : "";
    $otherRestrictions = htmlspecialchars($_POST['otherRestrictions'] ?? "");

    // Prepare and send request to save dietary restrictions
    $request = [
        "type" => "dietRestrictions",
        "session_token" => $session_token,
        "dietaryRestrictions" => $dietType,
        "otherRestrictions" => $otherRestrictions
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
    </style>
</head>
<body>
    <h2>Set Your Dietary Restrictions and Concerns</h2>

    <!-- Dietary Restrictions Form -->
    <form method="POST" action="">
        <!-- Dietary Restrictions --> 
        <div class="form-section">
            <label>Dietary Restrictions (select all that apply):</label><br>
            <?php
            $dietary_options = [
                "kosher",
                "vegetarian",
                "vegan",
                "pescatarian",
                "keto-friendly",
                "pork-free",
                "alcohol-free"
            ];
            foreach ($dietary_options as $diet) {
                $checked = in_array($diet, $dietaryRestrictions) ? "checked" : "";
                echo "<input type='checkbox' name='dietaryRestrictions[]' value='$diet' $checked> $diet<br>";
            }
            ?>
        </div>

        <!-- Other Restrictions Section -->
        <div class="form-section">
            <label for="otherRestrictions">Other Restrictions (optional):</label><br>
            <input type="text" id="otherRestrictions" name="otherRestrictions" placeholder="e.g., low sodium, low sugar" value="<?php echo htmlspecialchars($otherRestrictions); ?>">
        </div>

        <input type="submit" name="setRestrictions" value="Save Restrictions" class="button">
    </form>

    <?php if (!empty($responseMessage)): ?>
        <div class="response-container">
            <p><?php echo $responseMessage; ?></p>
        </div>
    <?php endif; ?>
</body>
<footer>
<div class="container">
    <div class="nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="reviews.php" class="button">Reviews</a>
        <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
    </div>
</footer>
</html>
