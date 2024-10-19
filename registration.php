<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Create a client for communicating with the RabbitMQ server
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create a registration request
    $request = array();
    $request['type'] = "register";
    $request['username'] = $username;
    $request['password'] = $password;

    // Send the registration request via RabbitMQ
    $response = $client->send_request($request);

    // If the registration is successful, redirect to login page
    if ($response === "User $username registered successfully") {
        header("Location: login.php");
        exit();  // Always call exit after header to stop further execution
    } else {
        echo "<p>Registration failed: " . htmlspecialchars($response) . "</p>";
    }
}
?>

<!-- Registration Form -->
<form action="registration.php" method="POST">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Register">
</form>

