<?php
// Database connection (updated with new credentials)
$dbHost = 'sql5.freesqldatabase.com';
$dbName = 'sql5737763';
$dbUser = 'sql5737763';
$dbPassword = 'xSGbpGyEpv';

try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$is_logged_in = false;
$username = null;
$time_remaining = 0;

// Check if the session token cookie is set
if (isset($_COOKIE['session_token'])) {
    $session_token = $_COOKIE['session_token'];

    // Query the database to verify the session token and check expiration
    $stmt = $db->prepare("SELECT username, session_expires FROM accounts WHERE session_token = ?");
    $stmt->execute([$session_token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $current_time = time(); // Current epoch time

        // Check if the session has expired
        if ($row['session_expires'] < $current_time) {
            // Session has expired, log out the user by clearing the cookie
            setcookie('session_token', '', time() - 3600, "/"); // Remove session token cookie
        } else {
            // Session is still active, extend the session expiration time
            $new_expires = $current_time + 30;
            $stmt = $db->prepare("UPDATE accounts SET session_expires = ? WHERE session_token = ?");
            $stmt->execute([$new_expires, $session_token]);

            // Extend the expiration time of the cookie
            setcookie('session_token', $session_token, $new_expires, "/");

            // Store the session details
            $time_remaining = $new_expires - $current_time;
            $username = $row['username'];
            $is_logged_in = true;
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

    <!-- JavaScript for auto-logout and popup warning if logged in -->
    <?php if ($is_logged_in): ?>
        <script>
            // Time remaining for the session (from PHP)
            let timeRemaining = <?php echo $time_remaining; ?>;

            // Show a warning 5 seconds before logout
            let warningTime = timeRemaining - 5;

            // Warn the user before logout
            if (warningTime > 0) {
                setTimeout(function() {
                    alert("You will be logged out in 5 seconds due to inactivity.");
                }, warningTime * 1000); // Convert to milliseconds
            }

            // Automatically log out the user after the session expires
            setTimeout(function() {
                window.location.href = "logout.php"; // Redirect to logout
            }, timeRemaining * 1000); // Convert to milliseconds
        </script>
    <?php endif; ?>
</body>
</html>

