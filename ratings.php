<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Get the session token from the cookie
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 30;
setcookie('session_token', $session_token, $expire_time, "/");

// Initialize message variable
$message = "";


// Submit a review if the form is posted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    
    // Retrieve form data
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    // Request to get the user's userID and username based on session token
    $userRequest = [
        "type" => "getUsernameBySession",
        "session_token" => $session_token
    ];
    $userResponse = $client->send_request($userRequest);

    // Check if the response is an array and contains 'success' key
    if (is_array($userResponse) && isset($userResponse['success']) && $userResponse['success']) {
        $userID = $userResponse['userID'];
        $username = $userResponse['username'];

        // Prepare the review request to store in the database
        $reviewRequest = [
            "type" => "submitReview",
            "userID" => $userID,
            "username" => $username,
            "rating" => $rating,
            "review" => $review
        ];
        $submitResponse = $client->send_request($reviewRequest);

        // Debugging: Output the response to verify
        var_dump($submitResponse); // Check if the response is being received
        $message = $submitResponse['message'] ?? "Unknown error occurred";
    } else {
        // Display the user response to debug if it’s not as expected
        var_dump($userResponse);
        $message = "Unable to retrieve user information.";
    }
}


// Retrieve all reviews to display
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$reviewRequest = ["type" => "getAllReviews"];
$reviews = $client->send_request($reviewRequest);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ratings and Reviews</title>
    <style>
        /* Basic styling for the ratings page */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
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
        .review-form, .reviews {
            margin-top: 20px;
            text-align: left;
        }
        .review-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .review-card h3 {
            color: #007bff;
            margin: 0;
        }
        .review-card p {
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Ratings and Reviews</h2>

    <!-- Navigation Buttons -->
    <div class="button-group">
        <a href="home.php" class="button">Home</a>
        <a href="meal_plan.php" class="button">Recipe Search</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Review Form -->
    <div class="review-form">
        <h3>Submit Your Review</h3>
        <form method="POST" action="">
            <label for="rating">Rating (1-5):</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required>
            <br><br>
            <label for="review">Review:</label><br>
            <textarea id="review" name="review" rows="4" cols="50" required></textarea>
            <br><br>
            <input type="submit" class="button" value="Submit Review">
        </form>
    </div>

    <!-- Display Reviews -->
    <div class="reviews">
        <h3>All Reviews</h3>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <h3><?php echo htmlspecialchars($review['username']); ?></h3>
                    <p><strong>Rating:</strong> <?php echo htmlspecialchars($review['rating']); ?>/5</p>
                    <p><?php echo htmlspecialchars($review['review']); ?></p>
                    <p><em><?php echo htmlspecialchars($review['timestamp']); ?></em></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to leave a review!</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
