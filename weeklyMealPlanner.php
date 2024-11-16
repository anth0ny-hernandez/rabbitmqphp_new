<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// // Refresh session token to extend expiration by another 90 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");


if ($_SERVER["REQUEST_METHOD"] == "GET") {

    //display foods received from search page and the form to choose which day & time of day it belongs to.
    var_dump($_GET['foods']);
    $_GET['foods'];
    foreach($_GET['foods'] as $food)
    {
        echo "$food";?> 
        <form action = "weeklyMealPlanner.php" method="POST">
            <select name ="day" id="day">
                <option value = "Sunday"> Sunday </option>
                <option value = "Monday"> Monday </option>
                <option value = "Tuesday"> Tuesday </option>
                <option value = "Wednesday"> Wednesday </option>
                <option value = "Thursday"> Thursday </option>
                <option value = "Friday"> Friday </option>
                <option value = "Saturday"> Saturday </option>
            </select>
                <select name ="meal_type" id="meal_type">
                    <option value = "Breakfast"> Breakfast </option>
                    <option value = "Lunch"> Lunch </option>
                    <option value = "Dinner"> Dinner </option>
    
            </select>
            <input type = "submit" name="createmealplanner" value = "Create Meal Planner">
        </form>
        <br> <br>
        
        <?php 
        
        //send the foods to dmz to get specific info to display in mealplanner table in the database. 


        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createmealplanner'])) {
            $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

            $foodDetailRequest = [
                "type" => "searchRecipe",
                "label" => $food ?? null,
            ];
            //get food details from dmz
            $foodDetails = $client->send_request($foodDetailRequest);
            
            $saveRequest = [
                "type" => "saveWeeklyMealPlan",
                "session_token" => $_COOKIE['session_token'],
                "foodDetails" => $foodDetails,
                "day"=> $_POST['day'],
                "meal_type"=>$_POST['meal_type']
            
            ];
            //send all the details to database for inserting
            $saveResponse = $client->send_request($saveRequest);
            $message = $saveResponse['success'] ? "Weekly meal plan updated successfully!" : "Failed to update meal plan.";
        }


    }
  
    exit;
}


//get meal plan info
$request = [
    "type" => "fetchWeeklyMealPlan",
    "session_token" => $_COOKIE['session_token']
];

$response = $client->send_request($request);
$currentMealPlan = $response['weeklyPlan'] ?? [];
$groupedRecipes = [];

if(isset($currentMealPlan)){

echo "<table>";
echo "<tr>";
echo "<th>Meal Type</th>";
//display in table. each row has meal_type, then meals for each day. 
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$mealType = ["Breakfast", "Lunch", "Dinner"];

// foreach ($daysOfWeek as $day) {
//     echo "<th>$day</th>";
//     echo "</tr>";


foreach($currentMealPlan as $meal){
    $day = $meal['day'];
    $mealTime = $meal['meal_type'];
    $recipe = $meal['recipe'];
    $calories = $meal['calories'];

    // Done so that the recipe is uniquely ID'd
    $groupedRecipes[$day][$mealTime][] = $recipe;
    
                                    }
}


echo "<table><tr><th>Days / Meal</th>"; // Creates HTML table
foreach($mealType as $meal) { // Sets Meal Types as table headers
    echo "<th>$meal</th>";
}
echo "</tr>";
foreach($weekdays as $day) { // Sets days of the week as the first cell in 7 rows
    echo "<tr>";
    echo "<td>$day</td>";
    foreach($mealType as $meal) {
        echo "<td>";
        if(isset($groupedRecipes[$day][$meal])) // Verifies that there is a recipe here
        {
            echo implode(" ", $groupedRecipes[$day][$meal]); // prints the recipe name
        } else {
            echo "No Recipe";
        }
        echo "</td>";
    }
    echo "</tr>";
}

echo "</tr>";
echo "</table>";

    // foreach ($currentMealPlan as $meal) {
    // echo "<tr>";
    //     echo "<td>{$meal['meal_type']}</td>";
    //     echo "<td>{$meal['recipe']}</td>";
    //     echo "</tr>";


    //                                     }


        // echo "<form method='POST' style='display:inline;'>
        //         <input type='hidden' name='recipe' value='{$meal['recipe']}'>
        //         <input type='hidden' name='day' value='$day'>
        //         <input type='hidden' name='meal_type' value='{$meal['meal_type']}'>
        //         <button type='submit' name='removeMeal' class='remove-button'>Remove</button>
        //       </form>";


//ignore lines 62-93 for now 
// $request = [
//     "type" => "fetchWeeklyMealPlan",
//     "session_token" => $_COOKIE['session_token']
// ];
// $response = $client->send_request($request);
// $currentMealPlan = $response['weeklyPlan'] ?? [];

// Process form submission to save new additions to the weekly meal plan
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createmealplanner'])) {
//     $weeklyPlan = $_POST['weekly_plan'];
//     $saveRequest = [
//         "type" => "updateWeeklyMealPlan",
//         "session_token" => $_COOKIE['session_token'],
//         "weeklyPlan" => $weeklyPlan
//     ];
    
//     $saveResponse = $client->send_request($saveRequest);
//     $message = $saveResponse['success'] ? "Weekly meal plan updated successfully!" : "Failed to update meal plan.";
// }



//fetch the recipe names from the checkboxes in "searchrecipe". Display them 

    // $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // // Collect form data for recipe search
    // $request = [
    //     "type" => "searchRecipe",
    //     "label" => $food ?? null,
    // ];

    //Display recipes with dropdown to select time of day and day of week.  
   
    
    // $recipeSearchResponse = $client->send_request($request);




    // $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // // Build the request with meal data
    // $request = [
    //     'type' => 'save_meals',
    //     'session_token' => $session_token,
    //     'meals' => $_POST['meals'] ?? []
    // ];

    // // Send the request and receive the response
    // $response = $client->send_request($request);

    // if ($response && isset($response['meals'])) {
    //     $meals = $response['meals'];
    // } else {
    //     echo "<p>Error: Unable to retrieve meal data.</p>";
    // }


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

        p {
            font-size: 20px;
        }
    </style>
</head>
<body>
    

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