#!/usr/bin/php
<?php
ob_start();
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

function doLogin($username,$password)
{
    // lookup username in databas
    // check password
    $login = new loginDB();
    return $login->validateLogin($username,$password);
    //return false if not valid
}

function doRegister($username,$password)
{
 //$db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');
 $db = new mysqli('172.22.241.239', "alvee-jalal", 'password', 'testdb');


  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  $insertQuery = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
  $insertQuery->bind_param("ss", $username, $hashedPassword);

  if ($insertQuery->execute()) {
      return "Registration successful";
  } else {
      return "Error: " . $db->error;
  }
  
      $result = $insertQuery->get_result();

      return $result;
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
    case "register":
      return doRegister($request['username'],$request['password']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}
ob_end_flush();
ob_implicit_flush(true);
echo "testRabbitMQServer BEGIN".PHP_EOL;

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>