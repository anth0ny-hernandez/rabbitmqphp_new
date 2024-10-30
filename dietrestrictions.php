<?php
$diet_type = $allergies = $other_restrictions = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $diet_type = ucfirst($_POST['diet_type']);
    $allergies = htmlspecialchars($_POST['allergies']);
    $other_restrictions = htmlspecialchars($_POST['other_restrictions']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Restriction Form</title>
    <style>
        header {
            background-color: #333;
            color: #fff;
            padding: 1rem;
            text-align: center;
        }

        body {
            font-family: Arial, sans-serif;
            margin-top: 50px;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: grey;
        }

        h1, h2 {
            font-size: 48px;
            font-weight: bold;
            color: lightgreen;
            text-align: center;
        }

        p {
            font-size: 20px;
            font-weight: bold;
            color: lightgreen;
        }

        .button {
            background-color: #ffffff;
            color: black;
            border: .5px solid #333;
            border-radius: 8px;
            transition: background-color 0.3s;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px;
        }

        button:hover {
            background-color: #555;
        }

        form {
            font-size: 20px;
            font-weight: bold;
            color: lightgreen;
        }

        label {
            font-size: 20px;
            font-weight: bold;
            color: lightgreen;
        }

        select, input[type="text"], textarea {
            font-size: 20px;
        }

        button[type="submit"] {
            background-color: #ffffff;
            color: black;
            border: .5px solid #333;
            border-radius: 8px;
            transition: background-color 0.3s;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px;
        }

        footer {
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <h2>Dietary Concerns and Restrictions</h2>
    <p>Please select and list any diet concerns and restrictions you may have</p>
    <form action="dietrestrictions.php" method="POST">
        <label for="diet_type">Select Diet Type:</label>
        <select name="diet_type" id="diet_type">
            <option value="None">No restrictions</option>
            <option value="vegetarian">Vegetarian</option>
            <option value="vegan">Vegan</option>
            <option value="halal">Halal</option>
            <option value="kosher">Kosher</option>
            <option value="gluten_free">Gluten-Free</option>
            <option value="lactose_free">Lactose-Free</option>
        </select>
        
        <br><br>
        
        <label for="allergies">Any Allergies (comma-separated):</label>
        <input type="text" name="allergies" id="allergies" placeholder="e.g., peanuts, shellfish">
        
        <br><br>

        <label for="other_restrictions">Other Restrictions:</label>
        <textarea name="other_restrictions" id="other_restrictions" placeholder="Specify any other dietary restrictions here..."></textarea>
        
        <br><br>

        <button type="submit">Submit</button>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
    <div class="response-container">
        <h2>Here are your selected Dietary Restrictions</h2>
        <p>Diet Type: <span class="highlight"><?php echo $diet_type; ?></span></p>
        <p>Allergies: <span class="highlight"><?php echo !empty($allergies) ? $allergies : "None"; ?></span></p>
        <p>Other Restrictions: <span class="highlight"><?php echo !empty($other_restrictions) ? $other_restrictions : "None"; ?></span></p>
    </div>
    <?php endif; ?>

</body>
<footer>
    <a href="index.php"><button class="button">Homepage</button></a>
    <a href="registration.php"><button class="button">Register</button></a>
    <a href="login.php"><button class="button">Login</button></a>
</footer>
</html>
