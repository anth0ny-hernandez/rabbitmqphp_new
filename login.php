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
    <title>Login</title>
    <style>
        /* Basic styling for a centered, clean login form */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f3f4f6;  /* Light background color for a clean look */
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px;
            width: 300px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            margin: 0 0 20px;
            color: #333;  /* Darker font for the title */
        }
        label {
            display: block;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            text-align: left;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;  /* Primary color for the button */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;  /* Darker shade on hover */
        }
        .error-message {
            color: #dc3545;  /* Red color for error */
            background-color: #f8d7da;  /* Light red background */
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if ($login_failed): ?>
            <div class="error-message">
                <?php echo $login_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
