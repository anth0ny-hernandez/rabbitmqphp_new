<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

$is_logged_in = false;
$username = null;

// Check if the session token cookie is set
if (isset($_COOKIE['session_token'])) {
    $session_token = $_COOKIE['session_token'];

    // Create a client to verify the session token with the backend (dbListener)
    $client = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");

    // Prepare the request to verify the session token
    $request = array();
    $request['type'] = "verify_session";
    $request['session_token'] = $session_token;

    // Send the request and get the response
    $response = $client->send_request($request);

    // Check if the session is valid
    if ($response['success']) {
        $current_time = time();
        $session_expires = $response['session_expires'];

        // Check if the session is still active
        if ($session_expires > $current_time) {
            // Session is active, extend the session expiration time
            $new_expires = $current_time + 30;

            // Update the cookie expiration time
            setcookie('session_token', $session_token, [
                'expires' => $new_expires,
                'path' => '/',
                'samesite' => 'Lax', // Set to 'Lax' or 'None' based on your requirement
                'secure' => false, // Use true if your site is served over HTTPS
                'httponly' => true
            ]);

            // Mark user as logged in
            $is_logged_in = true;
            $username = $response['username'];
        } else {
            // Session expired, clear the cookie
            setcookie('session_token', '', time() - 3600, "/");
            header("Location: login.php");
            exit();
        }
    } else {
        // Invalid session token, clear the cookie
        setcookie('session_token', '', time() - 3600, "/");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        h1 {
            font-size: 48px;
            font-weight: bold;
        }
        .button {
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px;
        }
    </style>
</head>
<body>
    <?php if ($is_logged_in): ?>
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <form action="logout.php" method="POST">
            <button type="submit" class="button">Logout</button>
        </form>
    <?php else: ?>
        <h1>Welcome to Our Site!</h1>
        <p>Feel free to browse around or log in to access more features.</p>
        <a href="login.php"><button class="button">Login</button></a>
        <a href="registration.php"><button class="button">Register</button></a>
    <?php endif; ?>

    <!-- JavaScript for auto-logout and warning if logged in -->
    <?php if ($is_logged_in): ?>
        <script>
            // Time remaining for the session
            let timeRemaining = 30; // 30 seconds

            // Show a warning 5 seconds before logout
            setTimeout(function() {
                alert("You will be logged out in 5 seconds due to inactivity.");
            }, (timeRemaining - 5) * 1000);

            // Automatically log out the user after the session expires
            setTimeout(function() {
                window.location.href = "logout.php"; // Redirect to logout
            }, timeRemaining * 1000);
        </script>
    <?php endif; ?>
</body>
</html>
