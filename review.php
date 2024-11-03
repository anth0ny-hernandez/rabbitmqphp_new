<?php
// Check if the form data has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form inputs
    $recipe_name = htmlspecialchars($_POST["recipe_name"]);
    $rating = intval($_POST["rating"]);
    $review = htmlspecialchars($_POST["review"]);

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rating and Review</title>
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

        .nav-buttons {
            margin-bottom: 20px;
        }

        .button {
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

        .button:hover {
            background-color: darkblue;
        }
        
        .logout-button {
            background-color: crimson;
        }

        .logout-button:hover {
            background-color: firebrick;
        }

        .form-section {
            margin-bottom: 20px;
            font-size: 18px;
        }

        select, input[type="text"], textarea {
            font-size: 20px;
        }

        label {
            font-size: 20px;
        }

        select, input[type="number"], textarea {
            font-size: 20px;
        }

        select, input[type="submit"], button {
            font-size: 20px;
        }

        h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Rate and Review Recipe</h2>
    <form action="review.php" method="POST">
        <div class="form-section">
            <label for="recipe_name">Recipe Name</label>
            <input type="text" id="recipe_name" name="recipe_name" required>
        </div>

        <div class="form-section">
            <label for="rating">Rating (1 to 5)</label>
            <select id="rating" name="rating" required>
                <option value="">Select Rating</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>
        </div>

        <div class="form-section">
            <label for="review">Your Review</label>
            <textarea id="review" name="review" rows="4" required></textarea>
        </div>

        <button type="submit">Submit Review</button>
    </form>
</div>

<!-- JavaScript to handle automatic logout after session expiration -->
<!-- <script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 30000); // 30 seconds
</script> -->
</body>
<footer>
    <div class="container nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="mealplannerform.php" class="button">Weekly Meal Planner Form</Form></a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
    </div>
</footer>
</html>
