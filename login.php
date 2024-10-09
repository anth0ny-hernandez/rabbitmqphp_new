<?php
// Include necessary files for RabbitMQ communication and DB processing
session_start();

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('dbProcessor.php');

function sendLoginRequest($username, $password) {
    // Create a RabbitMQ client
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Prepare the request payload
    $request = array();
    $request['type'] = "login";
    $request['username'] = $username;
    $request['password'] = $password;

    // Send the request to the RabbitMQ server and get a response
    $response = $client->send_request($request);
    return $response;
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Send the login request to RabbitMQ
    $result = sendLoginRequest($username, $password);

    // Check if login was successful
    if ($result['status'] == 'success') {
        // Set session token and redirect to the home page
        $_SESSION['session_token'] = $result['session_token'];
        $_SESSION['expires_at'] = time() + 20;  // Session expires in 2 minutes
        header("Location: home.php");
        exit();
    } else {
        // Display error message if login fails
        echo $result['message'];
    }
}


?>

<!-- HTML form for login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login Here</h1>
    <form method="POST" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
