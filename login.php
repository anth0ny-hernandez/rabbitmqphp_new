<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create a client to send the login request to RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Prepare the request
    $request = array();
    $request['type'] = "login";
    $request['username'] = $username;
    $request['password'] = $password;

    // Send the request and get the response
    $response = $client->send_request($request);

    // Check the response from the RabbitMQ server
    if ($response['success']) {
        // Login successful, set the session token cookie
        $session_token = $response['session_token'];
        $expire_time = time() + 30; // Cookie expires in 30 seconds

        // Set the session token cookie with appropriate attributes
        setcookie('session_token', $session_token, [
            'expires' => $expire_time,
            'path' => '/',
            'samesite' => 'Lax', // Set to 'Lax' or 'None' based on your requirement
            'secure' => false, // Use true if your site is served over HTTPS
            'httponly' => true
        ]);

        // Redirect to the home page
        header("Location: home.php");
        exit();
    } else {
        // Log the error message if login fails
        error_log("Login failed: " . $response['message']);
        echo "<p>Login failed: " . htmlspecialchars($response['message']) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
