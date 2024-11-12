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
    <title>Ratings and Reviews</title>
    <style>
        /* Page styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
            background-color: lightgrey;
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
            color: white;
            background-color: blue;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background-color: darkblue;
        }
        
        .logout-button {
            background-color: crimson;
        }

        .logout-button:hover {
            background-color: firebrick;
        }

        .form-section {
            margin-bottom: 20px;
            font-size: 18px;
            align: center;
        }

        select, input[type="text"], textarea {
            font-size: 20px;
        }

        label {
            font-size: 20px;
        }

        select, input[type="number"], textarea {
            font-size: 20px;
            margin: 10px
        }

        select, input[type="submit"], button {
            font-size: 20px;
        }

        h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="container">
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
        <br>
        <textarea name="feedback" placeholder="Write your review..." required><?php echo htmlspecialchars($feedback); ?></textarea>
        <br>
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

<!-- JavaScript to handle automatic logout after session expiration -->
<script>
    // setTimeout(function() {
    //     document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    //     window.location.href = 'login.php';
    // }, 90000); // 90 seconds
</script>
</body>
<footer>
    <div class="container nav-buttons">
        <a href="home.php" class="button">Home</a>
        <a href="search.php" class="button">Recipe Search</a>
        <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
        <a href="recommendations.php" class="button">Recommendations</a>
        <a href="mealplannerform.php" class="button">Weekly Meal Planner Form</Form></a>
        <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner</a>
        <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
    </div>
</footer>
</html>
