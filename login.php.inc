<?php

class loginDB
{
private $logindb;

public function __construct()
{
	//$this->logindb = new mysqli("127.0.0.1","testUser","12345","testdb");
	$this->logindb = new mysqli('172.22.241.239', "alvee-jalal", 'password', 'testdb');

	if ($this->logindb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

public function validateLogin($username,$password)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	$statement = "select * from users where username = '$un'";
	$response = $this->logindb->query($statement);

	while ($row = $response->fetch_assoc())
	{
		echo "checking password for $username".PHP_EOL;


		if (password_verify($pw, $row['password'])) {
			// Generate session token and store it in the database
			// Source for alphanumeric string generation: https://stackoverflow.com/questions/1846202/how-to-generate-a-random-unique-alphanumeric-string
			$bytes = random_bytes(20);
			$sessionToken = (bin2hex($bytes));
			$updateQuery = $this->logindb->prepare("UPDATE users SET session_token=? WHERE username=?");
			$updateQuery->bind_param("ss", $sessionToken, $username);
			$updateQuery->execute();
			// setcookie('session_token', $sessionToken, time() + 3600, "/");
 			echo "passwords match for $username".PHP_EOL;
			return 1;// password match
			
} else {
		echo "Invalid password for $username".PHP_EOL;
			return "Invalid password";
		}
	
	   
	}
// 		if ($row["password"] == $pw)
// 		{
// 			echo "passwords match for $username".PHP_EOL;
// 			return 1;// password match
// 		}
// 		echo "passwords did not match for $username".PHP_EOL;
// 	}
// 	return 0;//no users matched username
// }
}

}
?>
