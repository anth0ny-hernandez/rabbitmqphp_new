<?php

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

    // echo $groupedRecipes['Monday']['Dinner'];
    // var_dump($groupedRecipes);
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
?>

<html>
    <style>
        /* To make the table not look so cramped */
        table, tr, th, td {
            border: 1px solid black;
            padding: 10px;
            margin: 10px;
        }
    </style>
</html>