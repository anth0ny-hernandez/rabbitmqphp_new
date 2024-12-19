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
    $email = $_POST['email'];

    // Create a registration request
    $request = array();
    $request['type'] = "register";
    $request['username'] = $username;
    $request['password'] = $password;
    $request['email'] = $email;

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>

<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* Page Styling */
    body {
        font-family: Arial, sans-serif;
        background: lightgrey;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        padding-top: 56px; /* For fixed navbar */
    }

    .register-container {
        max-width: 600px;
        width: 100%;
        padding: 20px;
        background-color: white;
        border: 1px solid black;
        border-radius: 8px;
        box-shadow: 0px 0px 50px lightgreen;
        text-align: center;
    }

    h2 {
        color: black;
    }

    .form-group label {
        font-weight: bold;
        color: darkslategrey;
        text-align: left;
        display: block;
    }

    .form-control {
        padding: 10px;
        border: 1px solid lightgray;
        border-radius: 5px;
    }

    .btn-custom {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        color: white;
        background-color: blue;
        border: none;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .btn-custom:hover {
        background-color: lightblue;
    }

    .register-container p {
        color: slategray;
        font-size: 14px;
    }

    /* Navbar Styling */
    .navbar-brand {
        color: lightgreen !important;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .nav-link {
        color: white !important;
    }

    .nav-link:hover {
        color: lightgreen !important;
    }
</style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form action="registration.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control"
                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                    title="Please enter a valid email address" required>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <input type="submit" value="Register" class="btn-custom">
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
