<?php
// Check if the session token cookie is set
var_dump($_COOKIE);
if (!isset($_COOKIE['session_token'])) {
    // If not set, redirect to login page
    header("Location: login.php");
    exit();
}

// Get the session token from the cookie
$session_token = $_COOKIE['session_token'];

// Optionally, verify the session token with the backend if required

// Refresh the cookie to extend the expiration by another 30 seconds
$expire_time = time() + 10;
setcookie('session_token', $session_token, $expire_time, "/");

// Display the home page content
echo "<h1>Welcome to the Home Page!</h1>";
echo "<p>You are logged in. You will be automatically logged out in 30 seconds.</p>";

// JavaScript to handle automatic logout after 30 seconds
echo "<script>
    setTimeout(function() {
        // Delete the session token cookie by setting its expiration date in the past
        document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        // Redirect to the login page
        window.location.href = 'login.php';
    }, 10000); // 30,000 milliseconds = 30 seconds
</script>";
if(time() > $expire_time) {
    unset($_COOKIE['session_token']);
}
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>
    
    <style>
        header {
    background-color: #333;
    color: #fff;
    padding: 1rem;
    text-align: center;
}

body {
    font-family: Arial, sans-serif;
    margin-top: 50px;
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background-color: grey;
}

h1 {
    font-size: 48px;
    font-weight: bold;
    color: black;
    background-color: lightgreen;
    text-align: center;
}

p {
    font-size: 20px;
    font-weight: bold;
    color: lightgreen;
}

.button {
    background-color: #ffffff;
    color: black;
    border: .5px solid #333;
    border-radius: 8px;
    transition: background-color 0.3s;
    padding: 10px 20px;
    font-size: 18px;
    margin: 10px;
}

button:hover {
    background-color: #555;
}

footer {
    margin-top: 100px;
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
        <h1>Welcome ARAY Nutritions!</h1>
        <p>Feel free to browse around or log in to access more features.</p>
        <a href="login.php"><button class="button">Login</button></a>
        <a href="registration.php"><button class="button">Register</button></a>
        <a href="dietrestrictions.php"><button class="button">Diet Restricitons</button></a>
        <a href="search.php"><button class="button">Search</button></a>
    <?php endif; ?>

    <!-- JavaScript for auto-logout and popup warning if logged in -->
    <?php if ($is_logged_in): ?>
        <script>
            // Time remaining for the session (from PHP)
            let timeRemaining = <?php echo $time_remaining; ?>;

            // Show a warning 5 seconds before logout
            let warningTime = timeRemaining - 5;

            // Warn the user before logout
            //set an alert window after a set time
            //source for alert timeout window: https://stackoverflow.com/questions/1962861/javascript-alert-box-with-timer
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

