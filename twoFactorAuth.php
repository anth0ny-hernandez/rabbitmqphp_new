<?php


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Weekly Meal Planner</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                background-color: #f8f9fa;
            }

            .button-group {
                margin-top: 20px;
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
                background-color: red;
            }
            .logout-button:hover {
                background-color: darkred;
            }
            .login-button {
                background-color: green;
            }
            .login-button:hover {
                background-color: darkgreen;
            }
        </style>
    </head>
    <body>
        <div class="button-group">
            <a href="search.php" class="button">Recipe Search</a>
            <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
            <a href="recommendations.php" class="button">Recommendations</a>
            <a href="review.php" class="button">Rate and Review</a>
            <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner </a>
            <a href="autoshopper.php" class="button">Autoshopper </a>
            <a href="logout.php" class="button logout-button">Logout</a>
        </div>
        <br>
        <form action="send_email.php" method="POST">
            <label for="email">Recipient Email</label>
            <input type="email" id="email" name="email" 
                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                title="Please enter a valid email address" required>
            <button type="submit">Send Email</button>
        </form>

    </body>
</html>