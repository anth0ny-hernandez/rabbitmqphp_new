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



echo "<table><tr><th>Days / Meal</th>";

foreach($mealType as $meal) {

    echo "<th>$meal</th>";

}

echo "</tr>";



foreach($daysOfWeek as $day) {

    echo "<tr>";

    echo "<td>$day</td>";

    foreach($mealType as $meal) {

        echo "<td>";

        if(isset($groupedRecipes[$day][$meal])) {

            echo implode(" ", $groupedRecipes[$day][$meal]);

        } else {

            echo "No Recipe";

        }

        echo "</td>";

    }

    echo "</tr>";

}

echo "</table>";

//get meal plan info



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

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightgray;
            padding-top: 80px; /* Prevent overlap with the fixed navbar */
        }

        .container-custom {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 20px lightgreen;
        }

        h2, h3 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
        }

        .meal-item {
            border: 1px solid lightgrey;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            box-shadow: 0px 0px 50px lightgreen;
        }

        .logout-button {
            background-color: crimson;
            color: white;
            padding: 5px 15px;
            font-size: 14px;
            border-radius: 4px;
        }

        .logout-button:hover {
            background-color: darkred;
        }

        .navbar-brand {
            color: lightgreen !important;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .nav-link {
            color: white !important;
        }

        .nav-link:hover {
            color: green !important;
        }

        .dropdown-menu a {
            color: black !important;
        }

        .dropdown-menu a:hover {
            background-color: green !important;
            color: white !important;
        }

        /* Additional Styling */
        .meal-day-title {
            font-size: 24px;
            color: black;
            margin-top: 20px;
        }

        .meal-description {
            font-size: 18px;
            color: dimgray;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Logout Button -->
            <a class="btn logout-button" href="logout.php">Logout</a>

            <a class="navbar-brand" href="#">ARAY</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Features
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="home.php">Home</a>
                            <a class="dropdown-item" href="search.php">Recipe Search</a>
                            <a class="dropdown-item" href="dietrestrictions.php">Diet Restrictions</a>
                            <a class="dropdown-item" href="recommendations.php">Recommendations</a>
                            <a class="dropdown-item" href="mealplannerform.php">Weekly Meal Planner Form</a>
                            <a class="dropdown-item" href="weeklyMealPlanner.php">Weekly Meal Planner</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Meal Plan Content -->
    <div class="container container-custom">
        <h2>Your Weekly Meal Plan</h2>

        <!-- Meal Plan for Each Day -->
        <?php if (!empty($meals)): ?>
            <?php foreach ($meals as $day => $mealData): ?>
                <div class="meal-item">
                    <h3 class="meal-day-title"><?php echo ucfirst($day); ?></h3>
                    <div class="meal-description"><strong>Breakfast:</strong> <?php echo htmlspecialchars($mealData['breakfast'] ?? ''); ?></div>
                    <div class="meal-description"><strong>Lunch:</strong> <?php echo htmlspecialchars($mealData['lunch'] ?? ''); ?></div>
                    <div class="meal-description"><strong>Dinner:</strong> <?php echo htmlspecialchars($mealData['dinner'] ?? ''); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No meals were submitted.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>