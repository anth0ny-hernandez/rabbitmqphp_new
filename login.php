<?php
unset($_COOKIE['session_token']);
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

var_dump($_COOKIE);
if($_COOKIE['session_token']) {
    unset($_COOKIE['session_token']);
}

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
        $expire_time = time() + 10; // Cookie expires in 30 seconds
        setcookie('session_token', $session_token, $expire_time, "/");

        // Redirect to the home page
        header("Location: index.php");
        exit();
    } else {
        // Login failed, show an error message
        echo "<p>Login failed: " . $response['message'] . "</p>";
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
