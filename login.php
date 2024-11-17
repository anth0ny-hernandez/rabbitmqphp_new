<?php
unset($_COOKIE['session_token']);
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

if($_COOKIE['session_token']) {
    unset($_COOKIE['session_token']);
}

// Check if the form is submitted
$login_failed = false;
$login_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create a client to send the login request to RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

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
        $expire_time = time() + 30; // Cookie expires in 30 seconds
        setcookie('session_token', $session_token, $expire_time, "/");

        // Redirect to the home page
        header("Location: home.php");
        exit();
    } else {
        // Login failed, set the error message
        $login_failed = true;
        $login_message = "Login failed: " . htmlspecialchars($response['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: lightgrey;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding-top: 56px; /* Space for navbar */
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 0px 50px lightgreen;
            text-align: center;
        }

        h2 {
            color: black;
        }

        .form-group label {
            color: black;
        }

        .btn-custom {
            width: 100%;
            color: white;
            background-color: blue;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: lightblue;
        }

        .error-message {
            color: firebrick;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
        }

        /*Navbar Brand Style*/
        .navbar-brand {
            color: lightgreen !important;
        }

        .navbar-brand {
            position: absolute;
            left: 50%;
            transform: translateX(-50%)
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

    <div class="login-container">
        <h2>Login</h2>

        <?php if ($login_failed): ?>
            <div class="error-message">
                <?php echo $login_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-custom">Login</button>
        </form>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
