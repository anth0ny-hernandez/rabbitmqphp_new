<?php
// Include necessary files for RabbitMQ communication
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if both fields are filled
    if (empty($username) || empty($password)) {
        echo "Username and password are required";
    } else {
        // Send the registration request to RabbitMQ
        $result = sendRegisterRequest($username, $password);
        echo $result;
    }
}
?>

<!-- Simple HTML form for user registration -->
<form method="POST" action="register.php">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit">Register</button>
</form>

