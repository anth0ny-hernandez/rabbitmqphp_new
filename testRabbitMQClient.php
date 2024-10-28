#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
unset($_COOKIE['session_token']);

var_dump($_COOKIE);
if($_COOKIE['session_token']) {
    unset($_COOKIE['session_token']);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create a client to send the login request to RabbitMQ
    //$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

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
        header("Location: home.php");
        exit();
    } else {
        // Login failed, show an error message
        echo "<p>Login failed: " . $response['message'] . "</p>";
    }
}

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
    if ($response) {
        header("Location: login.php");
        exit();  // Always call exit after header to stop further execution
    } else {
        echo "Registration failed: " . $response;
    }
}

//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
