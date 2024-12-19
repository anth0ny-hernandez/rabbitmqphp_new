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

// Initialize variables to store current restrictions
$dietaryRestrictions = [];
$otherRestrictions = "";
$responseMessage = "";

// Check if dietary restrictions are already saved
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$request = [
    "type" => "getDietRestrictions",
    "session_token" => $session_token
];
$response = $client->send_request($request);

if ($response['success']) {
    // Populate the form fields with existing data
    $dietaryRestrictions = explode(", ", $response['dietaryRestrictions']);
    $otherRestrictions = $response['otherRestrictions'];
} else {
    $responseMessage = "No dietary restrictions saved yet.";
}

// Handle form submission to save new restrictions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setRestrictions'])) {
    $dietType = isset($_POST['dietaryRestrictions']) ? implode(", ", $_POST['dietaryRestrictions']) : "";
    $otherRestrictions = htmlspecialchars($_POST['otherRestrictions'] ?? "");

    // Prepare and send request to save dietary restrictions
    $request = [
        "type" => "dietRestrictions",
        "session_token" => $session_token,
        "dietaryRestrictions" => $dietType,
        "otherRestrictions" => $otherRestrictions
    ];

    $response = $client->send_request($request);
    $responseMessage = $response['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Dietary Restrictions</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Page Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: lightgrey;
            padding-top: 80px; /* Adjusted for navbar spacing */
        }

        .container-custom {
            max-width: 800px;
            margin: 30px auto; /* Centered and spaced */
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 50px lightgreen;
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 25px;
            text-align: center;
        }

        .examples {
            font-size: 20px;
            text-align: center;
            margin-top: 10px;
            padding-left: 20px;
        }

        .examples span {
            display: block;
            margin-top: 5px;
        }

        .form-section {
            margin-bottom: 20px;
            font-size: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 18px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            color: white;
            background-color: blue;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background-color: darkblue;
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

        .logout-button {
            background-color: crimson !important;
            color: white !important;
            border-radius: 4px;
            padding: 5px 15px;
            font-size: 14px;
        }

        .logout-button:hover {
            background-color: darkred !important;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Logout Button -->
            <a class="btn logout-button" href="logout.php">Logout</a>

            <!-- Navbar Brand -->
            <a class="navbar-brand" href="#">ARAY</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <!-- Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Features
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="home.php">Home</a>
                            <a class="dropdown-item" href="search.php">Recipe Search</a>
                            <a class="dropdown-item" href="recommendations.php">Recommendations</a>
                            <a class="dropdown-item" href="review.php">Rate and Review</a>
                            <a class="dropdown-item" href="mealplannerform.php">Weekly Meal Planner Form</a>
                            <a class="dropdown-item" href="weeklyMealPlanner.php">Weekly Meal Planner</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container container-custom">
        <h2>Set Your Dietary Restrictions and Concerns</h2>
        <h2>Examples:</h2>
            <div class="examples">
                <span>Vegetarian</span>
                <span>Pescatarian</span>
                <span>Pork-free</span>
                <span>Alcohol-free</span>
            </div>
    <br>
    
    <!-- Dietary Restrictions Form -->
    <form method="POST" action="">
        <div class="form-section">
            <label for="dietRestrictions">Diet Restrictions (optional):</label><br>
            <input type="text" id="dietRestrictions" name="dietRestrictions" placeholder="e.g., low sodium, low sugar" value="<?php echo htmlspecialchars($dietRestrictions); ?>">
        </div>
            <input type="submit" name="setRestrictions" value="Save Restrictions" class="button">
    </form>

        <?php if (!empty($responseMessage)): ?>
            <div class="response-container">
                <p><?php echo $responseMessage; ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
