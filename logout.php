<?php
require_once('login.php.inc');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('authenticate.php');


if(isset($_COOKIE['session_token']))
{
    $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');
    $updateQuery = $db->prepare("UPDATE users SET session_token=NULL WHERE username=?");
    $updateQuery->bind_param("ss", $sessionToken, $username);
    $updateQuery->execute();
    setcookie('session_token', NULL, "", "/");
}

?>


    <script>
        // Source for alert to run after certain time to enable logout and token expiration:
        // https://stackoverflow.com/questions/12591953/redirect-to-other-page-on-alert-box-confirm
setTimeout("if(confirm('Do you want to stay logged in?')){ window.location.href = '/webpages/Homepage.html' } else{ window.location.href = 'login.php'}",200000);
</script>

<?php


?>