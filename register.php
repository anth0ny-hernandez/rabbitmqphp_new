<?php
ob_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files for RabbitMQ communication and DB processing
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('dbProcessor.php');

session_start();

function sendRegisterRequest($username, $password) {
    // Create a RabbitMQ client
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Prepare the request payload
    $request = array();
    $request['type'] = "register";
    $request['username'] = $username;
    $request['password'] = $password;

    // Send the request to the RabbitMQ server and get a response
    $response = $client->send_request($request);
    return $response;
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if both fields are filled
    if (empty($username) || empty($password)) {
        echo "Username and password are required";
    } else {
        // Send the registration request to RabbitMQ
       $response = sendRegisterRequest($username, $password);

// Check if $response is an array before accessing its elements
if (is_array($response) && isset($response['status']) && $response['status'] == 'success') {
    ob_clean();  // Clears the output buffer, just in case
    header("Location: login.php");
    exit();
} else {
    // Handle the case where $response is not an array or doesn't have the expected structure
    error_log("Registration failed or unexpected response: " . print_r($response, true));
    echo "Registration failed.";
}

    }
}
?>

<!-- HTML: Add Login button and form for registration -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Register Here</h1>
    <form method="POST" action="register.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit" name="register">Register</button>
    </form>

    <br><br>

    <!-- Login Button: In case the user is already registered -->
    <form action="login.php">
        <button type="submit">Already Registered? Login Here</button>
    </form>
</body>
</html>

