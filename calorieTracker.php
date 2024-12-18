<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 30 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");

// Variables for calorie tracker
$user_id = 1; // Replace with session-based user ID
$date = date('Y-m-d');
$calories = 0;
$goal = 2000; // Default daily calorie goal
$message = "";

// Fetch current daily calories and goal
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$request = [
    "type" => "getDailyCalories",
    "user_id" => $user_id,
    "date" => $date
];
$response = $client->send_request($request);

if ($response['success'] && isset($response['data'])) {
    $calories = $response['data']['calories'] ?? 0;
    $goal = $response['data']['goal'] ?? $goal;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logCalories'])) {
        $mealCalories = (int)$_POST['calories'];

        $logRequest = [
            "type" => "logCalories",
            "user_id" => $user_id,
            "calories" => $mealCalories
        ];
        $logResponse = $client->send_request($logRequest);
        $message = $logResponse['message'];
        header("Location: calorieTracker.php");
        exit();
    }

    if (isset($_POST['updateGoal'])) {
        $newGoal = (int)$_POST['goal'];

        $goalRequest = [
            "type" => "updateCalorieGoal",
            "user_id" => $user_id,
            "goal" => $newGoal
        ];
        $goalResponse = $client->send_request($goalRequest);
        $message = $goalResponse['message'];
        header("Location: calorieTracker.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calorie Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        .form-section input, .form-section button {
            padding: 10px;
            font-size: 16px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-section button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .form-section button:hover {
            background-color: #0056b3;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary h2 {
            color: #333;
        }
        .summary p {
            color: #555;
        }
        .message {
            color: green;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            margin-bottom: 20px;
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
        .logout-button {
            background-color: #dc3545;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="calorieTracker.php" class="button">Calorie Tracker</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <h1>Calorie Tracker</h1>

    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="summary">
        <h2>Today's Calorie Summary</h2>
        <p><strong>Calories Consumed:</strong> <?php echo $calories; ?> kcal</p>
        <p><strong>Calorie Goal:</strong> <?php echo $goal; ?> kcal</p>
        <p><strong>Remaining:</strong> <?php echo max($goal - $calories, 0); ?> kcal</p>
    </div>

    <div class="form-section">
        <h3>Log a Meal</h3>
        <form method="POST">
            <input type="number" name="calories" placeholder="Enter meal calories" required>
            <button type="submit" name="logCalories">Log Calories</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Update Calorie Goal</h3>
        <form method="POST">
            <input type="number" name="goal" placeholder="Enter new calorie goal" value="<?php echo $goal; ?>" required>
            <button type="submit" name="updateGoal">Update Goal</button>
        </form>
    </div>
</div>

</body>
</html>
