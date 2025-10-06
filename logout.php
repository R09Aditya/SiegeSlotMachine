<?php
session_start();
session_destroy();


setcookie("siege_user", "", time() - 3600, "/");

header("Location: login_signup.php");
exit();
?>
