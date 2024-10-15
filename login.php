<?php 
require_once('login.php.inc');
require_once('path.inc');
require_once('get_host_info.inc');
if(isset($_COOKIE['session_token']))
{
    $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');
    $updateQuery = $db->prepare("UPDATE users SET session_token=NULL WHERE username=?");
    $updateQuery->bind_param("ss", $sessionToken, $username);
    $updateQuery->execute();
    setcookie('session_token', NULL, "", "/");
}
?>
<!DOCTYPE html>
<html>
<head>
<title> Login </title>
</head>

<form action = "authenticate.php" method = "POST" id="login_form">
    <label> Username: </label>
    <input type = "text" name = "username" id = "username"/>
    <label> Password: </label>
    <input type = "text" name = "password" id = "password"/>

    <button type="submit">Login</button>
    </form>

</html>
<?php 


?>