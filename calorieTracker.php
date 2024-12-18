<?php
require_once('rabbitMQLib.inc');


// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");

// Initialize variables
$errorMessage = "";
$successMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $session_token = $_COOKIE['session_token'];

    $request = [
        "type" => "addCalorieEntry",
        "session_token" => $session_token,
        "date" => $_POST['date'],
        "time" => $_POST['time'],
        "food_name" => $_POST['food_name'],
        "calories" => (int)$_POST['calories'],
    ];

    $response = $client->send_request($request);
    var_dump($response);
    die();

    if ($response['success']) {
        $successMessage = "Food entry added successfully!";
    } else {
        $errorMessage = $response['message'] ?? "Failed to add entry.";
    }
}


// Fetch all calorie entries for the user
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$fetchRequest = [
    "type" => "getCalorieEntries",
    "session_token" => $_COOKIE['session_token']  // Use session token from cookies
];
$entriesResponse = $client->send_request($fetchRequest);
$entries = $entriesResponse['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calorie Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
        }
        .chart-container {
            max-width: 800px;
            margin: auto;
            margin-top: 20px;
        }
        .form-container, .chart-container {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 10px;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .button-group {
            margin-top: 20px;
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
    </style>
</head>
<body>

<div class="button-group">
    <a href="home.php" class="button">Home</a>
    <a href="meal_plan.php" class="button">Recipe Search</a>
    <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
    <a href="recommendations.php" class="button">Recipe Recommendations</a>
    <a href="reviews.php" class="button">Ratings and Reviews</a>
    <a href="calorie_tracker.php" class="button">Calorie Tracker</a>
    <a href="logout.php" class="button logout-button">Logout</a>
</div>

<div class="form-container">
    <h2>Calorie Tracker</h2>
    <?php if ($successMessage): ?>
        <p class="success-message"><?php echo $successMessage; ?></p>
    <?php elseif ($errorMessage): ?>
        <p class="error-message"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>
        <br><br>
        <label for="time">Time:</label>
        <input type="time" id="time" name="time" required>
        <br><br>
        <label for="food_name">Food Name:</label>
        <input type="text" id="food_name" name="food_name" required>
        <br><br>
        <label for="calories">Calories:</label>
        <input type="number" id="calories" name="calories" required>
        <br><br>
        <button type="submit" name="add_entry">Add Entry</button>
    </form>
</div>

<div class="chart-container">
    <h2>Your Calorie Log</h2>
    <?php if (!empty($entries)): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Food Items</th>
                    <th>Total Calories</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $date => $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($date); ?></td>
                        <td>
                            <ul>
                                <?php foreach ($data['items'] as $item): ?>
                                    <li><?php echo htmlspecialchars($item['time'] . " - " . $item['food_name'] . " (" . $item['calories'] . " cal)"); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td><?php echo htmlspecialchars($data['total_calories']); ?> cal</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No calorie entries yet. Start logging your meals!</p>
    <?php endif; ?>
</div>

</body>
</html>
