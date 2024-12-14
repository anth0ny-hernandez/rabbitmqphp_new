<?php
ob_start();

// Will become active for errors when it comes to validating the OTP
$otp_failed = false;
$failed_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Gets code value from form
    $code = $_POST['code'];
    $twoFA_DB = new mysqli('localhost', 'testUser', '12345', 'testdb');

    // uses Code field for UID, also gets time at which code will expire
    $getValues = "SELECT expires, OTP FROM 2FA WHERE OTP = ?";
    $stmt = $twoFA_DB->prepare($getValues);
    $stmt->bind_param("s", $code); // Sees if USER inputted OTP matches DB-stored OTP
    if(!$stmt->execute()) {
        error_log("Error in binding OTP: " . $twoFA_DB->error);
        echo "Error in binding OTP:" . $twoFA_DB->error . "\n";
    } else {
        $result = $stmt->get_result();
    }
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc(); // Turns results into array
        $futureTime = $row['expires']; // get Expiration set by table
        $getTableCode = $row['OTP']; // get OTP set by table
        $currentTime = time(); // Time at this moment for comparison
        ob_end_flush();
        echo "Future time: " . $futureTime . "<br>";
        echo "Current time: " . $currentTime . "<br>";
        echo "OTP is " . $getTableCode . "<br>";
    } else {
        echo "OTP doesn't exist!\n";
        error_log("Error in fetching field with OTP: " . $twoFA_DB->error);
        echo "Error in fetching field with OTP:" . $twoFA_DB->error . "\n";
    }

    // ie, that expiration hasnt passed and user OTP matches table OTP
    if($futureTime > $currentTime && $getTableCode === $code) {
        header("Location: twoFactorAuth.php");
        exit();
    } elseif($futureTime < $currentTime) {
        $otp_failed = true;
        $mssg = "The one-time password has expired. Please request a new one.";
        $failed_message = "Authentication failed: " . $mssg;
    }
    elseif($getTableCode !== $code) {
        $otp_failed = true;
        $mssg = "The passcodes do not match. Please try again.\n";
        $failed_message = "Invalid code: " . $mssg;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <style>
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
        <?php if ($otp_failed): ?>
            <div class="error-message">
                <?php echo $failed_message; ?>
            </div>
        <?php endif; ?>

        <form action="codeConfirm.php" method="POST">
            <label for="code">Enter your OTP: </label>
            <input type="code" id="code" name="code" required>
            <button type="submit">Confirm Passcode</button>
    </body>
</html>