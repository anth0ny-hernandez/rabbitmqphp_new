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

// Initialize variables
$username = "";
$rating = "";
$feedback = "";
$successMessage = "";
$errorMessage = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitReview'])) {
    $username = htmlspecialchars($_POST['username']);
    $rating = (int)$_POST['rating'];
    $feedback = htmlspecialchars($_POST['feedback']);

    // Send review data to the database server via RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $request = [
        "type" => "submitReview",
        "username" => $username,
        "rating" => $rating,
        "feedback" => $feedback
    ];
    $response = $client->send_request($request);

    if ($response['success']) {
        $successMessage = "Thank you! Your review has been submitted.";
        // Clear the form data after successful submission
        $username = "";
        $rating = "";
        $feedback = "";
    } else {
        $errorMessage = "Error submitting review: " . $response['message'];
    }
}

// Fetch all reviews to display on the page
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$fetchRequest = [
    "type" => "fetchReviews"
];
$reviewsResponse = $client->send_request($fetchRequest);
$reviews = $reviewsResponse['reviews'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ratings and Reviews</title>
    <style>
        /* Basic styling for the page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        .button-group {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .review-form input, .review-form select, .review-form textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .review-list {
            margin-top: 20px;
            text-align: left;
        }
        .review-item {
            background-color: #f7f7f7;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .error-message {
            color: #dc3545;
        }
        .success-message {
            color: #28a745;
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
        <a href="dietRestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recipe Recommendations</a>
        <a href="reviews.php" class="button">Ratings and Reviews</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </div>

    <!-- Success or Error Messages -->
    <?php if ($successMessage): ?>
        <p class="success-message"><?php echo $successMessage; ?></p>
    <?php elseif ($errorMessage): ?>
        <p class="error-message"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- Review Submission Form -->
    <form method="POST" class="review-form">
        <input type="text" name="username" placeholder="Your Name" required value="<?php echo htmlspecialchars($username); ?>">
        <select name="rating" required>
            <option value="">Rate out of 5</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo ($i == $rating) ? "selected" : ""; ?>><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <textarea name="feedback" placeholder="Write your review..." required><?php echo htmlspecialchars($feedback); ?></textarea>
        <button type="submit" name="submitReview" class="button">Submit Review</button>
    </form>

    <!-- Display All Reviews -->
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

<script>
    setTimeout(function() {
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        window.location.href = 'login.php';
    }, 90000); // 30 seconds
</script>

</body>
</html>
