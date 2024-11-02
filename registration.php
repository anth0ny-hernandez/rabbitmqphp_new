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

<!-- Registration Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        /* Page styling */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: lightgrey;
        }

        .register-container {
            background-color: white;
            padding: 30px;
            width: 300px;
            border-radius: 10px;
            box-shadow: 0px 0px 50px lightgreen;
            text-align: center;
        }

        h2 {
            margin: 0 0 20px;
            color: black;
        }

        label {
            display: block;
            font-weight: bold;
            color: dimgray;
            margin-bottom: 5px;
            text-align: left;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid lightgray;
            border-radius: 5px;
            font-size: 14px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            color: white;
            background-color: blue;  /* Main color for button */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: darkblue;  /* On hover the color gets darker */
        }

        .register-container p {
            color: slategray;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form action="registration.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>


