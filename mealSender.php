<?php

$formSub = [
    'meal_planner' => [
        ['calories' => 300, 'recipe' => 'Gorditas'],
        ['calories' => 600, 'recipe' => 'Chiles con Atun'],
        ['calories' => 1000, 'recipe' => 'Milanesa'],
        ['calories' => 1000, 'recipe' => 'Birria'],
        ['calories' => 750, 'recipe' => 'Mole Poblano'],
        ['calories' => 500, 'recipe' => 'Pozole'],
        ['calories' => 250, 'recipe' => 'Arroz'],
    ]
];
    // if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // $_GET['foods'];
        echo "<form action='testing.php' method='POST'>";
        // foreach ($_GET['foods'] as $index => $food) {
        foreach($formSub['meal_planner'] as $index => $food) {
            // take out??
            // echo "<input type='hidden' name='food[]' value='$tempvar'>";
            echo $food['recipe']; // ==> the way to recipe value
            // echo htmlspecialchars($food['recipe']);
            // index is key (ie 0, 1) & food is value (ie gordita, cals)
            echo "<br>";
?>
<html>
            <label for="day_<?php echo $index; ?>">Select Day:</label>
            <select name="day[]" id="day_<?php echo $index; ?>">
                <option value="Sunday">Sunday</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
            </select>

            <label for="meal_type_<?php echo $index; ?>">Select Meal Type:</label>
            <select name="meal_type[]" id="meal_type_<?php echo $index; ?>">
                <option value="Breakfast">Breakfast</option>
                <option value="Lunch">Lunch</option>
                <option value="Dinner">Dinner</option>
            </select>

            <!-- Hiden input for the meal name? -->
             <input type="hidden" name="food[]" 
                value="<?php echo htmlspecialchars($food['recipe']); ?>">
            <br><br>
</html>
<?php
        }
        echo "<input type='submit' name='createmealplanner' value='Create Meal Planner'>";
        echo "</form>";
    // }

?>



<?php

// create array to mimic alvee's db and send that thru form or whatever
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createmealplanner'])) {
    $foods = $_POST['food'] ?? [];
    $days = $_POST['day'] ?? [];
    $mealTypes = $_POST['meal_type'] ?? [];

    foreach ($foods as $index => $food) {
        $day = $days[$index] ?? null;
        $mealType = $mealTypes[$index] ?? null;

        if ($food && $day && $mealType) {
            // echo "$food is $mealType on $day<br><br>";
            echo "$food, $mealType<br>";
            // $foodDetailRequest = [
                // "type" => "searchRecipe",
                // "label" => $food,
            // ];
            // $foodDetails = $client->send_request($foodDetailRequest);

            // $saveRequest = [
            //     "type" => "saveWeeklyMealPlan",
            //     "session_token" => $_COOKIE['session_token'],
            //     "foodDetails" => $foodDetails,
            //     "day" => $day,
            //     "meal_type" => $mealType,
            // ];

            // $saveResponse = $client->send_request($saveRequest);
            // $message = $saveResponse['success'] ? "Weekly meal plan updated successfully!" : "Failed to update meal plan.";
        } else {
            echo "Nomogus";
        }
    }
}
*/
?>