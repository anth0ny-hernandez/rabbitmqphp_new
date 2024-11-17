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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid d-flex justify-content-between">
            <a class="navbar-brand mx-auto" href="#">ARAY</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registration.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container register-container">
        <h2>Register</h2>
        <form action="registration.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-custom">Register</button>
        </form>
    </div>
    
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>



