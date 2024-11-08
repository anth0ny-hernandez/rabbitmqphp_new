<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (isset($_COOKIE['session_token'])) {
    $session_token = $_COOKIE['session_token'];

    // Create a RabbitMQ client to send a logout request
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $request = [
        "type" => "logout",
        "session_token" => $session_token
    ];
    $response = $client->send_request($request);

    // Check if the logout was successful
    if ($response['success']) {
        // Remove the session token cookie by setting its expiration to a past time
        setcookie('session_token', '', time() - 3600, "/");
    }
}

// Redirect to homepage after logout
header("Location: home.php");
exit();
?>
