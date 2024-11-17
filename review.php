<?php
require_once('rabbitMQLib.inc');

// // Check if the session token cookie is set
// if (!isset($_COOKIE['session_token'])) {
//     header("Location: login.php");
//     exit();
// }

// // Refresh session token to extend expiration by another 90 seconds
// $session_token = $_COOKIE['session_token'];
// $expire_time = time() + 90;
// setcookie('session_token', $session_token, $expire_time, "/");

// // Initialize variables
// $username = "";
// $rating = "";
// $feedback = "";
// $successMessage = "";
// $errorMessage = "";

// // Process form submission
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitReview'])) {
//     $username = htmlspecialchars($_POST['username']);
//     $rating = (int)$_POST['rating'];
//     $feedback = htmlspecialchars($_POST['feedback']);

//     // Send review data to the database server via RabbitMQ
//     $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
//     $request = [
//         "type" => "submitReview",
//         "username" => $username,
//         "rating" => $rating,
//         "feedback" => $feedback
//     ];
//     $response = $client->send_request($request);

//     if ($response['success']) {
//         $successMessage = "Thank you! Your review has been submitted.";
//         // Clear the form data after successful submission
//         $username = "";
//         $rating = "";
//         $feedback = "";
//     } else {
//         $errorMessage = "Error submitting review: " . $response['message'];
//     }
// }

// // Fetch all reviews to display on the page
// $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
// $fetchRequest = [
//     "type" => "fetchReviews"
// ];
// $reviewsResponse = $client->send_request($fetchRequest);
// $reviews = $reviewsResponse['reviews'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratings and Reviews</title>

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

        .review-form input,
        .review-form select,
        .review-form textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid black;
            border-radius: 4px;
        }

        .review-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid black;
        }

        .error-message {
            color: crimson;
        }

        .success-message {
            color: green;
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
            color: lightgreen !important;
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

    <div class="container container-custom">
        <h2>Ratings and Reviews</h2>
        <!-- Success or Error Messages -->
        <?php if ($successMessage): ?>
            <p class="success-message"><?php echo $successMessage; ?></p>
        <?php elseif ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <!-- Review Submission Form -->
        <form method="POST" class="review-form">
            <input type="text" name="username" placeholder="Your Name" required value="<?php echo htmlspecialchars($username); ?>">
            <br>
            <select name="rating" required>
                <option value="">Rate out of 5</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($i == $rating) ? "selected" : ""; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <textarea name="feedback" placeholder="Write your review..." required><?php echo htmlspecialchars($feedback); ?></textarea>
            <br>
            <button type="submit" name="submitReview" class="btn btn-primary btn-block" style="background-color: blue;">Submit Review</button>
        </form>
        <br>
        
        <div class="review-list">
            <h3>User Reviews</h3>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <p><strong><?php echo htmlspecialchars($review['username']); ?></strong> (Rated: <?php echo $review['rating']; ?>/5)</p>
                        <p><?php echo htmlspecialchars($review['feedback']); ?></p>
                        <p><small>Posted on: <?php echo htmlspecialchars($review['created_at']); ?></small></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to leave a review!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
