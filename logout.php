<?php
require_once('login.php.inc');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('dbProcessor.php');
require_once('authenticate.php');

 
?>


    <script>
        // Source for alert to run after certain time to enable logout and token expiration:
        // https://stackoverflow.com/questions/12591953/redirect-to-other-page-on-alert-box-confirm
setTimeout("if(confirm('Do you want to stay logged in?')){ window.location.href = '/webpages/Homepage.html' } else{ window.location.href = 'login.php'}",4000);
</script>


