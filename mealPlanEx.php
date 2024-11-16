<?php
require_once('rabbitMQLib.inc');

// // Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: login.php");
//     exit();
// }

// // Refresh session token to extend expiration by another 90 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 90;
// setcookie('session_token', $session_token, $expire_time, "/");
// $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

/****************** Block Used for Table Iteration ******************/

// To allow iteration of the days in a loop
$weekdays = [
    'Sunday', 
    'Monday', 
    'Tuesday', 
    'Wednesday', 
    'Thursday', 
    'Friday', 
    'Saturday',  
];

// To separate parts of the day into meal times
$mealType = ["Breakfast", "Lunch", "Dinner"];

// Meant to mimic the array returned from Alvee's database
$formSub = [
    'meal_planner' => [
        ['calories' => 300, 'day' => 'Sunday', 'meal' => 'Lunch', 'recipe' => 'Gorditas'],
        ['calories' => 600, 'day' => 'Thursday', 'meal' => 'Dinner', 'recipe' => 'Chiles con Atun'],
        ['calories' => 1000, 'day' => 'Friday', 'meal' => 'Breakfast', 'recipe' => 'Milanesa'],
        ['calories' => 1000, 'day' => 'Tuesday', 'meal' => 'Dinner', 'recipe' => 'Birria'],
        ['calories' => 750, 'day' => 'Saturday', 'meal' => 'Breakfast', 'recipe' => 'Mole Poblano'],
        ['calories' => 500, 'day' => 'Wednesday', 'meal' => 'Lunch', 'recipe' => 'Pozole'],
        ['calories' => 250, 'day' => 'Monday', 'meal' => 'Dinner', 'recipe' => 'Arroz'],
    ]
];

// creates empty array to hold the values from the database
$groupedRecipes = [];
// verifies that the DB returned array is not empty
if(isset($formSub['meal_planner'])) {
    $recipeArray = $formSub['meal_planner'];

    // allows every recipe to be identifiable by day, meal type, and recipe
    foreach($recipeArray as $entry) {
        $day = $entry['day'];
        $mealTime = $entry['meal'];
        $recipe = $entry['recipe'];
        $calories = $entry['calories'];

        // Done so that the recipe is uniquely ID'd
        $groupedRecipes[$day][$mealTime][] = $recipe;
        
    }
}
/****************** END BLOCK ******************/

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

        .meal-planner {
            max-width: 900px;
            margin: 0 auto;
            /* border: 1px solid black; */
        }
        .week-header {
            font-size: 24px;
            font-weight: bold;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
            /* border: 1px solid black; */
        }
        .daily-field {
            margin-bottom: 30px;
            /* border: 1px solid black; */
        }
        .day-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            /* border: 1px solid black; */
            width: 205px;
        }
        .meals-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            /* border: 1px solid black; */
        }
        .meal-item {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex: 1;
            min-width: 180px;
            max-width: 220px;
            /* border: 1px solid black; */
        }
        .meal-type {
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            font-weight: bold;
            flex: 1;
            min-width: 180px;
            max-width: 220px;
            margin-bottom: 30px;
            /* border: 1px solid black; */
        }
        .week-day {
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            font-weight: bold;
            flex: 1;
            min-width: 180px;
            max-width: 220px;
            /* border: 1px solid black; */
            /* For alignment purposes */
            margin: auto;
            text-align: center;
        }
        .meal-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .meal-item-name {
            padding: 10px;
            font-size: 14px;
            color: #555;
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
<div class="meal-planner">
    <div class="week-header">Your Meal Plan For This Week</div>
    
    <!-- Details the Meal types -->
    <div class="meals-container">
            <div class="meal-type">Days / Meals</div> <!-- If spacing affected, revert to meal-item -->
            <div class="meal-type">Breakfast</div>
            <div class="meal-type">Lunch</div>
            <div class="meal-type">Dinner</div>
    </div>

    <!-- PHP Table Iteration -->
     <?php
        // Iterates thru DOTW to make 7 rows with the days as starting cells
        foreach($weekdays as $day) {
            // Creates the rows holding DOTW & daily meal info
            echo "<div class='daily-field'>";
                echo "<div class='meals-container'>";
                    echo "<div class='week-day'>$day</div>";
                    // Creates the cell holding ONE meal's worth of info
                    foreach($mealType as $meal){
                        echo "<div class='meal-item'>";
                        // validates that there's a meal on this specific day for this specific meal
                        if(isset($groupedRecipes[$day][$meal]))
                        {
                            $food = implode(" ", $groupedRecipes[$day][$meal]);
                            echo "<img src='https://www.southernliving.com/thmb/m3m-JadISxPYjCOcASeSw3mTmI0=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Sunny_Side_Up_Eggs_007-fe57becdb5c4473092cba5e14e407bfc.jpg' alt='Pumpkin Walnut Breakfast Bowl'>";
                            echo "<div class='meal-item-name'>$food</div>";
                        } else {
                            echo "No Recipe";
                        }
                        echo "</div>";
                    }
                echo "</div>";
            echo "</div>";
        }
     ?>
</div>

</body>
</html>
