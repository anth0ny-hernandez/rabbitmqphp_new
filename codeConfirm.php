<?php
ob_start();

// Will become active for errors when it comes to validating the OTP
$otp_failed = false;
$failed_message = '';

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Refresh session token to extend expiration by another 30 seconds
$session_token = $_COOKIE['session_token'];
$expire_time = time() + 90;
setcookie('session_token', $session_token, $expire_time, "/");

require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Create a client for communicating with the RabbitMQ server
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request = array();
    $request['type'] = "verify2fa";             // Sends request to dbListener to enable 2FA
    $request['cookieUID'] = $session_token;     // Serves as a way to identify current user
    // Gets code value from form
    $code = $_POST['code'];
    $request['otp'] = $code;
    $response = $client->send_request($request);

    if($response["success"]) {
        // ie, that expiration hasnt passed and user OTP matches table OTP
        if($response["isExpired"] && $response["codeMatch"]) {
            header("Location: twoFactorAuth.php");
            ob_end_flush(); // Might work somewhere else too
            exit();
        } 
        elseif(!$response["isExpired"] && !$response["codeMatch"]) {
            $otp_failed = true;
            $mssg = "The one-time password has expired. Please request a new one.\n";
            $failed_message = "2FA verification failed: " . $mssg;
        }
        elseif(!$response["codeMatch"]) {
            $otp_failed = true;
            $mssg = "The passcodes do not match. Please try again.\n";
            $failed_message = "2FA verification failed: " . $mssg;
        }
        elseif(!$response["isExpired"]) {
            $otp_failed = true;
            $mssg = "The one-time password has expired. Please request a new one.\n";
            $failed_message = "2FA verification failed: " . $mssg;
        }
        else {
            $otp_failed = true;
            $mssg = "The user does not exist.\n";
            $failed_message = "2FA verification failed: " . $mssg;
        }
    } 
    else {
        $otp_failed = true;
        $mssg = $request["message"];
        $failed_message = "Unexpected error, OTP doesn't exist: " . $mssg;
    }

    
}

?>

<!DOCTYPE html>
<html lang="en">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .button-group {
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            color: white;
            background-color: blue;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }
        .button:hover {
            background-color: darkblue;
        }
        .logout-button {
            background-color: red;
        }
        .logout-button:hover {
            background-color: darkred;
        }
        .login-button {
            background-color: green;
        }
        .login-button:hover {
            background-color: darkgreen;
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
    <body>
        <div class="container">
            <center><h1>Enable 2-Factor Authentication</h1></center>
            <div class="button-group">
                <a href="search.php" class="button">Recipe Search</a>
                <a href="dietrestrictions.php" class="button">Diet Restrictions</a>
                <a href="recommendations.php" class="button">Recommendations</a>
                <a href="review.php" class="button">Rate and Review</a>
                <a href="weeklyMealPlanner.php" class="button">Weekly Meal Planner </a>
                <a href="autoshopper.php" class="button">Autoshopper </a>
                <a href="logout.php" class="button logout-button">Logout</a>
            </div>
            <h2>Please enter the OTP that was emailed to you.</h2>
            <?php if ($otp_failed): ?>
                <div class="error-message">
                    <?php echo $failed_message; ?>
                </div>
            <?php endif; ?>
            <form action="codeConfirm.php" method="POST">
                <label for="code">Enter your OTP: </label>
                <input type="text" id="code" name="code" required>
                <button type="submit">Confirm Passcode</button>
            </form>
        </div>
    </body>
</html>