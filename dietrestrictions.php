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

$diet_type = $allergies = $other_restrictions = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dietaryRestrictions'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    $diet_type = ucfirst($_POST['dietaryRestrictions']);
    $allergies = isset($_POST['allergies']) ? implode(", ", $_POST['allergies']) : "None";
    $other_restrictions = htmlspecialchars($_POST['otherRestrictions']);
    
    $data = [
        'diet_type' => $diet_type,
        'allergies' => $allergies,
        'other_restrictions' => $other_restrictions
    ];

    // Send the data to RabbitMQ
    $response = $client->send_request($data);
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
    </style>
</head>
<body>
    <h2>Set Your Dietary Restrictions and Concerns</h2>

    <!-- Dietary Restrictions Form -->
    <form method="POST" action="">
        
        <!-- Dietary Restrictions -->
        <div class="form-section">
            <label for="dietaryRestrictions">Dietary Restrictions (optional):</label>
            <select id="dietaryRestrictions" name="dietaryRestrictions">
                <option value="" <?php echo $diet_type == "" ? "selected" : ""; ?>>None</option>
                <option value="halal" <?php echo $diet_type == "Halal" ? "selected" : ""; ?>>Halal</option>
                <option value="kosher" <?php echo $diet_type == "Kosher" ? "selected" : ""; ?>>Kosher</option>
                <option value="vegetarian" <?php echo $diet_type == "Vegetarian" ? "selected" : ""; ?>>Vegetarian</option>
                <option value="vegan" <?php echo $diet_type == "Vegan" ? "selected" : ""; ?>>Vegan</option>
                <option value="gluten-free" <?php echo $diet_type == "Gluten-free" ? "selected" : ""; ?>>Gluten-Free</option>
                <option value="dairy-free" <?php echo $diet_type == "Dairy-free" ? "selected" : ""; ?>>Dairy-Free</option>
                <option value="nut-free" <?php echo $diet_type == "Nut-free" ? "selected" : ""; ?>>Nut-Free</option>
            </select>
        </div>

        <!-- Allergies Section -->
        <div class="form-section">
            <label for="allergies">Allergies (select all that apply, if an allergy you have is not here specify in other restrictions.):</label><br>
            <?php
            $allergy_options = ["Peanuts", "Tree Nuts", "Soy", "Dairy", "Gluten", "Shellfish", "Eggs", "Fish"];
            foreach ($allergy_options as $allergy) {
                $checked = (isset($_POST['allergies']) && in_array($allergy, $_POST['allergies'])) ? "checked" : "";
                echo "<input type='checkbox' name='allergies[]' value='$allergy' $checked> $allergy<br>";
            }
            ?>
        </div>

        <!-- Other Restrictions Section -->
        <div class="form-section">
            <label for="otherRestrictions">Other Restrictions (optional):</label><br>
            <input type="text" id="otherRestrictions" name="otherRestrictions" placeholder="e.g., low sodium, low sugar" value="<?php echo htmlspecialchars($other_restrictions); ?>">
        </div>

        <input type="submit" name="setRestrictions" value="Save Restrictions" class="button">
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
    <div class="response-container">
        <h2>Here are your selected Dietary Restrictions</h2>
        <p>Diet Type: <span class="highlight"><?php echo $diet_type; ?></span></p>
        <p>Allergies: <span class="highlight"><?php echo $allergies; ?></span></p>
        <p>Other Restrictions: <span class="highlight"><?php echo !empty($other_restrictions) ? $other_restrictions : "None"; ?></span></p>
    </div>
    <?php endif; ?>
</body>
<footer>
<div class="container">
    <div class="nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Meal Plan</a>
        <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
    </div>
</footer>
</html>
