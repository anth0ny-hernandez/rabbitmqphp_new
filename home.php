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
