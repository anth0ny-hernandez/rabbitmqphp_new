<?php
require_once('login.php.inc');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
$db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');
// $db = new mysqli('172.22.241.239', "alvee-jalal", 'password', 'testdb');
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$username = filter_input(INPUT_POST,'username');
$password = filter_input(INPUT_POST, 'password');
$request = array();
$request['type'] = "login";
$request['username'] = $username; 
$request['password'] = $password;
$response = $client->send_request($request);

$query = "SELECT session_token FROM users WHERE username=?";
$statement = $db->prepare($query);
$statement->bind_param("s", $username);
if($statement->execute())
{
    $result = $statement->get_result();
    echo "success!";
    $resultToken=$result->fetch_all();
    $sessionToken = $resultToken[0][0];
    echo "client receiveds $sessionToken".PHP_EOL;
}

else
{
    echo "fail";
}

setcookie('session_token', $sessionToken, time() + 3600, "/");
echo "client receiveds $sessionToken".PHP_EOL;
print_r($response);
echo $response;
return $response;




// function doLogin($username, $password){

//     $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');


//     $query = "select * FROM users WHERE username=?";
//     $statement = $db->prepare($query);
//     $statement->bind_param('s', $username);
//     $statement->execute();

//     $thepassword = $statement->get_result();
// }
// ?>

<?php 

?>