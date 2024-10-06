<?php 
require_once('login.php.inc');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('dbProcessor.php');
doLogout($username);
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