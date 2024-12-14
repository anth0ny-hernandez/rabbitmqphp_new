<?php
require_once('rabbitMQLib.inc');
//initially set tracker to false
$trackerStart=false;


// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 30 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 30;
setcookie('session_token', $session_token, $expire_time, "/");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['trackCalories'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    $trackerStart=true;
    // Collect form data for recipe search
    $request = [
        "type" => "trackCalories",
        "recipe" => $_POST['label'] ?? null,
        "day" => $_POST['day'] ?? null,
        "date" => $_POST['date'] ?? null,
        "time" => $_POST['time'] ?? null,
        "goal" => $_POST['goal'] ?? null,
        "calorieseaten" => $_POST['calorieseaten'] ?? null,
        "session_token" => $_COOKIE['session_token'] ?? null

    ];
    // set tracker to true once form is submitted, making form disappear

    

    $calorieTrackerResponse = $client->send_request($request);
}
// 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nextDayCalories'])) {

    $trackerStart=false;



}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Calorie Tracker</title>
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
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
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .logout-button {
            background-color: #dc3545;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        .meal-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>


<div class="container">
    <div class="button-group">
        <a href="home.php" class="button">Home Page</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="review.php" class="button">Rate and Review</a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner </a>
        <a href="autoshopper.php" class="button">Autoshopper </a>
        <a href="calorietracker.php" class="button">Daily Calorie Tracker </a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>


    <h2>Daily Calorie Tracker</h2>

  <?php 
  echo $trackerStart ? 'true' : 'false';  {?>
    <!-- Recipe Search Form -->
     <div id="trackerTrigger">
    <form method="POST" action="calorietracker.php">
        <label for="label">Recipe Name:</label>
        <input type="text" id="label" name="label" placeholder="e.g., pasta, salad" required>
        <br><br>

        <label for="day">Day:</label>
        <input type="text" id="day" name="day" placeholder="Friday">
        <br><br>

        <label for="date">Date:</label>
        <input type="text" id="date" name="date" placeholder="May 20th">
        <br><br>

        <label for="time">Time:</label>
        <input type="text" id="time" name="time" placeholder="6:00PM">
        <br><br>

        <label for="goal">Goal:</label>
        <input type="number" id="goal" name="goal" placeholder="1000">
        <br><br>

        <label for="calorieseaten">Calories Eaten:</label>
        <input type="number" id="calorieseaten" name="calorieseaten" placeholder="100">
        <br><br>

        <input type="submit" id = "trackCalories" name="trackCalories" value="Save For Today">  <input type="submit" name="nextDayCalories" id =nextDayCalories value="Next Day">
    </div>
  
    </form>


    <div id="tracker">
    <form method="POST" action="calorietracker.php">
    <label for="label">Recipe Name:</label>
        <input type="text" id="label" name="label" placeholder="e.g., pasta, salad" required>
        <br><br>

        <label for="day">Day:</label>
        <input type="text" id="day" name="day" placeholder="Friday" disabled>
        <br><br>

        <label for="date">Date:</label>
        <input type="text" id="date" name="date" placeholder="May 20th" disabled>
        <br><br>

        <label for="time">Time:</label>
        <input type="text" id="time" name="time" placeholder="6:00PM" >
        <br><br>

        <label for="goal">Goal:</label>
        <input type="number" id="goal" name="goal" placeholder="1000" disabled>
        <br><br>

        <label for="calorieseaten">Calories Eaten:</label>
        <input type="number" id="calorieseaten" name="calorieseaten" placeholder="100">
        <br><br>

        <input type="submit" id = "trackCalories" name="trackCalories" value="Save For Today">  <input type="submit" id = nextDayCalories name="nextDayCalories" value="Next Day">
    </form>
    </div>

    <?php }        
?>
    <script>
const buttonNextDay = document.getElementById("nextDayCalories");
buttonNextDay.addEventListener("click", function() {
   

    trackerStart = false;
        document.getElementById("trackerTrigger").style.visibility="view";

    

})
</script>

<?php 
if($trackerStart===true) { ?>

 <script>
document.getElementById("trackerTrigger").style.visibility="hidden";
document.getElementById("tracker").style.visibility="view";
</script>
<?php }

else{
?>
    <script>
    document.getElementById("tracker").style.visibility="hidden";
    document.getElementById("trackerTrigger").style.visibility="view";
    </script>
<?php
}

?>


    <!-- Display Logic for Recipe Search Results -->
    <form method="GET" action="weeklyMealPlanner.php"> 
    <?php if (isset($recipeSearchResponse['error'])): ?>
        <p><?php echo htmlspecialchars($recipeSearchResponse['error']); ?></p>
    <?php elseif (isset($recipeSearchResponse['hits']) && !empty($recipeSearchResponse['hits'])): ?>
        <h3>Search Results:</h3>
        <?php foreach ($recipeSearchResponse['hits'] as $hit): ?>
            <div class="meal-item">
                <strong><?php echo htmlspecialchars($hit['recipe']['label']); ?></strong><br>
                <a href="<?php echo htmlspecialchars($hit['recipe']['url']); ?>" target="_blank">View Recipe</a><br>
                Calories: <?php echo round($hit['recipe']['calories']); ?><br>
                <?php if (!empty($hit['recipe']['image'])): ?>
                    <img src="<?php echo htmlspecialchars($hit['recipe']['image']); ?>" alt="<?php echo htmlspecialchars($hit['recipe']['label']); ?>" width="100"><br>
                <?php endif; ?>
                <input type = "checkbox" id="<?php echo $hit['recipe']['label']?>" name="foods[]" value="<?php echo $hit['recipe']['label']?>"> <label for ="add to meal plan"> Add to Meal Plan</label>
            </div>
            
        <?php endforeach; ?>
        <input type = "submit" value="Submit Meals for Planner">
    </form>
    <?php else: ?>
        
    <?php endif; ?>

</div>

<!-- JavaScript to handle automatic logout after session expiration -->
<!-- <script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 90000); // 30 seconds
</script> -->

</body>
</html>