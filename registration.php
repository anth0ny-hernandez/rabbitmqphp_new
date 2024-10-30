<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

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
    if ($response) {
        header("Location: login.php");
        exit();  // Always call exit after header to stop further execution
    } else {
        echo "Registration failed: " . $response;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>

<style>
        header {
    background-color: #333;
    color: #fff;
    padding: 1rem;
    text-align: center;
}

body {
    font-family: Arial, sans-serif;
    margin-top: 50px;
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background-color: grey;
}

h1 {
    font-size: 48px;
    font-weight: bold;
    color: black;
    background-color: lightgreen;
    text-align: center;
}

h2 {
    font-size: 48px;
    font-weight: bold;
    color: lightgreen;
}

p {
    font-size: 20px;
    font-weight: bold;
    color: lightgreen;
}

.button {
    background-color: #ffffff;
    color: black;
    border: .5px solid #333;
    border-radius: 8px;
    transition: background-color 0.3s;
    padding: 10px 20px;
    font-size: 18px;
    cursor: pointer;
    margin: 10px;
}

button:hover {
    background-color: #555;
}

form{
    color: lightgreen;
}

footer {
    margin-top: 100px;
}
    </style>

<body>
    <h2>Registration</h2>
<!-- Registration Form -->
<form action="registration.php" method="POST">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Register">
</form>
</body>
<footer>
<a href="index.php"><button class="button">Homepage</button></a>
<a href="login.php"><button class="button">Login</button></a>
<a href="dietrestrictions.php"><button class="button">Diet Restricitons</button></a>
</footer>
</html>