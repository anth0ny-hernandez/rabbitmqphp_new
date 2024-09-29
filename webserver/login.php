<!DOCTYPE html>
<html>
<head>
<title> Login </title>
</head>

<form action = "authenticate.php" method = "POST" id="login_form" >
    <label> Username: </label>
    <input type = "text" name = "username" id = "username" />
    <label> Password: </label>
    <input type = "text" name = "password" id = "password" />

    <input type = "button" id="submit_login_form" value="SUBMIT"/>
</form>