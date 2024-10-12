<?php
require_once('login.php.inc');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$username = filter_input(INPUT_POST,'username');
$password = filter_input(INPUT_POST, 'password');
echo "<h1> hi </h1>";
$request = array();
$request['type'] = "login";
$request['username'] = $username; 
$request['password'] = $password;
$response = $client->send_request($request);

echo "client received response".PHP_EOL;
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