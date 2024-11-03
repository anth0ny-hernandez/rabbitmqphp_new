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

$dietType = $allergyType = $otherRestrictions = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dietaryRestrictions'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    $dietType = ucfirst($_POST['dietType']);
    $allergyType = isset($_POST['allergyType']) ? implode(", ", $_POST['allergyType']) : "None";
    $otherRestrictions = htmlspecialchars($_POST['otherRestrictions']);
    
    $request = [
        "type" => "dietRestrictions",
        "label" => $_POST['label'] ?? null,
        "healthLabels" => $_POST['healthLabels'] ?? null,
        'dietType' => $dietType,
        'allergyType' => $allergyType,
        'otherRestrictions' => $otherRestrictions,
    ];

    $response = $client->send_request($request);
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
                "Kosher",
                "Vegetarian",
                "Vegan",
                "Pescatarian",
                "Keto-Friendly",
                "Pork-Free",
                "Alcohol-Free"
            ];
            foreach ($dietary_options as $diet) {
                $checked = (isset($_POST['dietaryRestrictions']) && in_array($diet, $_POST['dietaryRestrictions'])) ? "checked" : "";
                echo "<input type='checkbox' name='dietaryRestrictions[]' value='$diet' $checked> $diet<br>";
            }
            ?>
        </div>

        <!-- Allergies -->
        <div class="form-section">
            <label for="allergyType">Allergies (select all that apply, if an allergy you have is not here specify in other restrictions.):</label><br>
            <?php
            $allergy_options = ["Peanuts", "Tree Nuts", "Soy", "Dairy", "Gluten", "Shellfish", "Eggs", "Fish"];
            foreach ($allergy_options as $allergy) {
                $checked = (isset($_POST['allergyType']) && in_array($allergy, $_POST['allergyType'])) ? "checked" : "";
                echo "<input type='checkbox' name='allergyType[]' value='$allergy' $checked> $allergy<br>";
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

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
    <div class="response-container">
        <p><?php echo $responseMessage; ?></p>
        <h2>Your Selected Dietary Restrictions</h2>
        <p>Diet Restrictions: <span class="highlight"><?php echo isset($_POST['dietaryRestrictions']) ? implode(", ", $_POST['dietaryRestrictions']) : "None"; ?></span></p>
        <p>Allergies: <span class="highlight"><?php echo isset($_POST['allergyType']) ? implode(", ", $_POST['allergyType']) : "None"; ?></span></p>
        <p>Other Restrictions: <span class="highlight"><?php echo !empty($otherRestrictions) ? $otherRestrictions : "None"; ?></span></p>
    </div>
    <?php endif; ?>

<!-- JavaScript to handle automatic logout after session expiration -->
<!-- <script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 30000); // 30 seconds
</script> -->
</body>
<footer>
<div class="container">
    <div class="nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="mealplannerform.php" class="button">Weekly Meal Planner Form</Form></a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
    </div>
</footer>
</html>