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
        /* Meal Planer Section START */
        .meal-planner {
            max-width: 900px;
            margin: 0 auto;
            margin-top: 50px;
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
        /* Meal Planner Section END */

        /* CSS For NavBar Buttons */
        .button-container {
            max-width: 1000px;
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
    </style>
</head>
<body>

<h2>Recipe Search</h2>

<div class="button-container">
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="reviews.php" class="button">Ratings and Reviews</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>
</div>

<div class="meal-planner">
    <div class="week-header">Your Meal Plan For This Week</div>
    
    <!-- Details the Meal types -->
    <div class="meals-container">
            <div class="week-day"></div> <!-- If spacing affected, revert to meal-item -->
            <div class="meal-type">Breakfast</div>
            <div class="meal-type">Lunch</div>
            <div class="meal-type">Dinner</div>
    </div>

    <!-- Sunday Section -->
    <div class="daily-field">
        <!-- Move meal types here if anything goes wrong -->
        <!-- If space is desired: <br> -->
        <div class="meals-container">
         <div class="week-day">Sunday</div>
            <div class="meal-item">
                <img src="https://www.southernliving.com/thmb/m3m-JadISxPYjCOcASeSw3mTmI0=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Sunny_Side_Up_Eggs_007-fe57becdb5c4473092cba5e14e407bfc.jpg" alt="Pumpkin Walnut Breakfast Bowl">
                <div class="meal-item-name">Pumpkin Walnut Breakfast Bowl</div>
            </div>
            <div class="meal-item">
                <img src="meal2.jpg" alt="No Bean Chili">
                <div class="meal-item-name">No Bean Chili</div>
            </div>
            <div class="meal-item">
                <img src="meal4.jpg" alt="Deli Meat + Sauerkraut">
                <div class="meal-item-name">Deli Meat + Sauerkraut</div>
            </div>
        </div>
    </div>

    <!-- Monday Section -->
    <div class="daily-field">
        <!-- Move meal types here if anything goes wrong -->
        <!-- If space is desired: <br> -->
        <div class="meals-container">
         <div class="week-day" >Monday</div>
            <div class="meal-item">
                <img src="https://www.southernliving.com/thmb/m3m-JadISxPYjCOcASeSw3mTmI0=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Sunny_Side_Up_Eggs_007-fe57becdb5c4473092cba5e14e407bfc.jpg" alt="Pumpkin Walnut Breakfast Bowl">
                <div class="meal-item-name">Pumpkin Walnut Breakfast Bowl</div>
            </div>
            <div class="meal-item">
                <img src="meal2.jpg" alt="No Bean Chili">
                <div class="meal-item-name">No Bean Chili</div>
            </div>
            <div class="meal-item">
                <img src="meal4.jpg" alt="Deli Meat + Sauerkraut">
                <div class="meal-item-name">Deli Meat + Sauerkraut</div>
            </div>
        </div>
    </div>

    <!-- Tuesday Section -->
    <div class="daily-field">
        <div class="meals-container">
            <div class="week-day">Tuesday</div>
            <div class="meal-item">
                <img src="meal5.jpg" alt="Bacon and Scrambled Eggs">
                <div class="meal-item-name">Bacon and Scrambled Eggs</div>
            </div>
            <div class="meal-item">
                <img src="meal6.jpg" alt="Shrimp Stir Fry">
                <div class="meal-item-name">Shrimp Stir Fry</div>
            </div>
            <div class="meal-item">
                <img src="meal7.jpg" alt="Italian Prosciutto Salad">
                <div class="meal-item-name">Italian Prosciutto Salad</div>
            </div>
        </div>
    </div>

    <!-- Wednesday Section -->
    <div class="daily-field">
        <div class="meals-container">
            <div class="week-day">Wednesday</div>
            <div class="meal-item">
                <img src="meal9.jpg" alt="AIP 'Grits'">
                <div class="meal-item-name">AIP "Grits"</div>
            </div>
            <div class="meal-item">
                <img src="meal10.jpg" alt="Steak Salad">
                <div class="meal-item-name">Steak Salad</div>
            </div>
            <div class="meal-item">
                <img src="meal11.jpg" alt="Salmon Salad with Winter Fruit">
                <div class="meal-item-name">Salmon Salad with Winter Fruit</div>
            </div>
        </div>
    </div>

    <!-- Thursday Section -->
    <div class="daily-field">
        <!-- Move meal types here if anything goes wrong -->
        <!-- If space is desired: <br> -->
        <div class="meals-container">
         <div class="week-day" >Thursday</div>
            <div class="meal-item">
                <img src="https://www.southernliving.com/thmb/m3m-JadISxPYjCOcASeSw3mTmI0=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Sunny_Side_Up_Eggs_007-fe57becdb5c4473092cba5e14e407bfc.jpg" alt="Pumpkin Walnut Breakfast Bowl">
                <div class="meal-item-name">Pumpkin Walnut Breakfast Bowl</div>
            </div>
            <div class="meal-item">
                <img src="meal2.jpg" alt="No Bean Chili">
                <div class="meal-item-name">No Bean Chili</div>
            </div>
            <div class="meal-item">
                <img src="meal4.jpg" alt="Deli Meat + Sauerkraut">
                <div class="meal-item-name">Deli Meat + Sauerkraut</div>
            </div>
        </div>
    </div>

    <!-- Friday Section -->
    <div class="daily-field">
        <div class="meals-container">
            <div class="week-day">Friday</div>
            <div class="meal-item">
                <img src="meal5.jpg" alt="Bacon and Scrambled Eggs">
                <div class="meal-item-name">Bacon and Scrambled Eggs</div>
            </div>
            <div class="meal-item">
                <img src="meal6.jpg" alt="Shrimp Stir Fry">
                <div class="meal-item-name">Shrimp Stir Fry</div>
            </div>
            <div class="meal-item">
                <img src="meal7.jpg" alt="Italian Prosciutto Salad">
                <div class="meal-item-name">Italian Prosciutto Salad</div>
            </div>
        </div>
    </div>

    <!-- Saturday Section -->
    <div class="daily-field">
        <div class="meals-container">
            <div class="week-day">Saturday</div>
            <div class="meal-item">
                <img src="meal9.jpg" alt="AIP 'Grits'">
                <div class="meal-item-name">AIP "Grits"</div>
            </div>
            <div class="meal-item">
                <img src="meal10.jpg" alt="Steak Salad">
                <div class="meal-item-name">Steak Salad</div>
            </div>
            <div class="meal-item">
                <img src="meal11.jpg" alt="Salmon Salad with Winter Fruit">
                <div class="meal-item-name">Salmon Salad with Winter Fruit</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
