<?php

require_once('rabbitMQLib.inc');



// Check if the session token cookie is set

if (!isset($_COOKIE['session_token'])) {

    header("Location: login.php");

    exit();

}



// Refresh session token to extend expiration by another 90 seconds

$session_token = $_COOKIE['session_token'];

$expire_time = time() + 90;

setcookie('session_token', $session_token, $expire_time, "/");

$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");



if ($_SERVER["REQUEST_METHOD"] == "GET") {

    //display foods received from search page and the form to choose which day & time of day it belongs to.
    $_GET['foods'];
    var_dump($_GET['foods']);

    foreach($_GET['foods'] as $food)

    {

        //fetch the recipe names from the checkboxes in "searchrecipe". Display them 

        echo "$food";

        ?>

        <form action="weeklyMealPlanner.php" method="POST">

            <input type="hidden" name="foods[]" value="<?php echo htmlspecialchars($food); ?>">

            <select name="day[]" id="day">

                <option value="Sunday">Sunday</option>

                <option value="Monday">Monday</option>

                <option value="Tuesday">Tuesday</option>

                <option value="Wednesday">Wednesday</option>

                <option value="Thursday">Thursday</option>

                <option value="Friday">Friday</option>

                <option value="Saturday">Saturday</option>

            </select>

            

            <select name="meal_type[]" id="meal_type">

                <option value="Breakfast">Breakfast</option>

                <option value="Lunch">Lunch</option>

                <option value="Dinner">Dinner</option>

            </select>

            <br><br>

        <?php

    }

    ?>

    <input type="submit" name="createmealplanner" value="Create Meal Planner">

    </form>

    <?php

}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createmealplanner'])) {

    $foods = $_POST['foods'];

    $days = $_POST['day'];

    $meal_types = $_POST['meal_type'];

    

    for ($i = 0; $i < count($foods); $i++) {

        $foodDetailRequest = [

            "type" => "searchRecipe",

            "label" => $foods[$i] ?? null,

        ];

        //get food details from dmz

        $foodDetails = $client->send_request($foodDetailRequest);

        
        foreach($foodDetails['hits'] as $hit)
        {
            if($hit['recipe']['label']==$foods[$i])
            {
                $foodDetailInsert['label'] = $hit['recipe']['label'];
                $foodDetailInsert['url'] = $hit['recipe']['url'];
                $foodDetailInsert['calories'] = $hit['recipe']['calories'];

            }
        }        

        $saveRequest = [

            "type" => "saveWeeklyMealPlan",

            "session_token" => $_COOKIE['session_token'],

            "foodDetailInsert" => $foodDetailInsert,

            "day" => $days[$i],

            "meal_type" => $meal_types[$i]

        ];

        //send all the details to database for inserting

        $saveResponse = $client->send_request($saveRequest);

        $message = isset($saveResponse['success']) && $saveResponse['success'] ? "Weekly meal plan updated successfully!" : "Failed to update meal plan.";

        echo $message;
    }
}
  

$request = [
    "type" => "fetchWeeklyMealPlan",
    "session_token" => $_COOKIE['session_token']
];

$response = $client->send_request($request);

$currentMealPlan = $response['weeklyPlan'] ?? [];
$groupedRecipes = [];

if(isset($currentMealPlan)){
    foreach($currentMealPlan as $meal){
        $day = $meal['day'];
        $mealTime = $meal['meal_type'];
        $recipe = $meal['recipe'];
        $groupedRecipes[$day][$mealTime][] = $recipe;
    }
}

// Display the meal plan table
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$mealType = ["Breakfast", "Lunch", "Dinner"];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Meal Plan</title>
    <style>
        /* Simple Styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background: lightgrey;
        }

        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid black;
            border-radius: 8px;
            box-shadow: 0px 0px 50px lightgreen;
            background: white;
        }
        
        table, tr, th, td {
            border: 1px solid black;
            padding: 10px;
            margin: 10px;
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

        p {
            font-size: 20px;
        }
    </style>
</head>
<body>
    
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

<!-- JavaScript to handle automatic logout after session expiration -->
<!-- <script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 90000); // 90 seconds
</script> -->
</body>
<footer>
    <div class="button-group">
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner </a>
        <a href="autoshopper.php" class="button">Autoshopper </a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
</footer>
</html>