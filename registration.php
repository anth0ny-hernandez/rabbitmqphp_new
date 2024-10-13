<?php
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

    // Debugging: Log that we are about to send the request
    echo "Sending registration request to RabbitMQ...<br>";

    // Send the registration request via RabbitMQ
    $response = $client->send_request($request);

    // Check if a response is received
    if ($response === false) {
        echo "Error: No response received from the RabbitMQ server.";
    } else {
        // Debugging: Print the response received from the RabbitMQ server
        echo "Registration response: ";
        print_r($response);
    }
}
?>

<!-- Registration Form -->
<form action="registration.php" method="POST">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Register">
</form>

