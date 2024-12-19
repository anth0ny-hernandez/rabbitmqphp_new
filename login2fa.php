<?php
ob_start();

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 90 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");

require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Check if the form is submitted
$login_failed = false;
$login_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['otp'];

    // Create a client to send the login request to RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Prepare the request
    $request = array();
    $request['type'] = "verify2fa";     // Reused since logic is very similar
    $request['otp'] = $code;
    $request['cookieUID'] = $session_token;

    // Send the request and get the response
    $response = $client->send_request($request);
    var_dump($response);

    if($response["success"]) {
        // ie, that expiration hasnt passed and user OTP matches table OTP
        if($response["isExpired"] && $response["codeMatch"]) {
            header("Location: home.php");
            ob_end_flush(); // Might work somewhere else too
            exit();
        } 
        elseif(!$response["isExpired"] && !$response["codeMatch"]) {
            $otp_failed = true;
            $mssg = "The one-time password has expired. Please request a new one.\n";
            $login_message = "2FA verification failed: " . $mssg;
        }
        elseif(!$response["codeMatch"]) {
            $otp_failed = true;
            $mssg = "The passcodes do not match. Please try again.\n";
            $login_message = "2FA verification failed: " . $mssg;
        }
        elseif(!$response["isExpired"]) {
            $otp_failed = true;
            $mssg = "The one-time password has expired. Please request a new one.\n";
            $login_message = "2FA verification failed: " . $mssg;
        }
        else {
            $otp_failed = true;
            $mssg = "The user does not exist.\n";
            $login_message = "2FA verification failed: " . $mssg;
        }
    } 
    else {
        $otp_failed = true;
        $login_message = "Unexpected error, OTP doesn't exist: " . $mssg;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Basic styling for a centered, clean login form */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f3f4f6;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            margin: 0 0 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: left;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if ($login_failed): ?>
            <div class="alert alert-danger">
                <?php echo $login_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login2fa.php">
            <div class="form-group">
                <label for="otp">One-time Password:</label>
                <input type="text" name="otp" id="otp" class="form-control" required>
            </div>
            <input type="submit" value="Login2FA" class="btn btn-primary btn-block">
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
