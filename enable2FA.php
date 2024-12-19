<?php
ob_start();

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

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request = array();
    $request['type'] = "enable2fa";             // Sends request to dbListener to enable 2FA
    $request['cookieUID'] = $session_token;     // Serves as a way to identify current user
    $request['answer'] = $_POST['enable2fa'];   // Sends user's yes/no to dbListener
    $response = $client->send_request($request);

    if($response["success"]) {
        $waitTime = 5;  // redirection countdown
        // ob_start(); --> Uncomment if putting it at start doesnt work
        header("Refresh: $waitTime; url=codeConfirm.php"); 	// Does the actual redirect
        echo "<h2>Email was successfully sent!<h2>"; 
        echo "You will shortly be redirected in $waitTime seconds\n";
        ob_end_flush();
        exit();
    } 
    else {
        header("Location: home.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Enable 2FA</title>
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
        </style>
    </head>
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
            <h2>Enabling Two-Factor Authentication</h2>
            <p>For extra security, you can enable 2FA. This is optional, and you
                can always enable it after. Confirming Yes will send you a time sensitive
                one-time password (OTP) to the email registered in your account. 
                Subsequent retries are allowed upon failure to provide either the OTP within
                the allotted time or the correct password. This is a permanent change.
            </p>
            <form action="enable2FA.php" method="POST">
                <button type="submit" name="enable2fa" value="yes">Yes, enable 2FA</button>
                <button type="submit" name="enable2fa" value="no">No, skip for now</button>
            </form>
        </div>
    </body>
</html>