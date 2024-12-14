<?php 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Further email sanitization for security
	$email = $_POST['email'];
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo "Not good email\n";
	} else {
		$saniEmail = escapeshellarg($email);
	}
	
	$script = 'send_email.sh'; 			// Describes filename to execute
	$code = random_int(100000, 999999); // Generates 6-digit OTP
	$code = (string)$code; 				// Explicitly casts OTP as a string

	$OTPdb = new mysqli('localhost', 'testUser', '12345', 'testdb');
	$expiration = time() + 90;
	$stmt = "INSERT INTO 2FA (enabled, OTP, expires)
			VALUES (TRUE, '$code', '$expiration')";

	if($OTPdb->query($stmt)) {
		$output = shell_exec("./$script $email $code"); 	// Executes bash script
		$waitTime = 5; 										// redirection countdown
		ob_start();
		header("Refresh: $waitTime; url=codeConfirm.php"); 	// Does the actual redirect
		echo "<h2>Email was successfully sent!<h2>"; 
		echo "You will shortly be redirected in $waitTime seconds\n";
		ob_end_flush();
		$stmt->close();		// Moving close statements breaks function, keep here
		$OTPdb->close();	// Moving close statements breaks function, keep here
		exit();
	} else {
		echo "Error: " . $stmt->error;
	}
}	

?>
