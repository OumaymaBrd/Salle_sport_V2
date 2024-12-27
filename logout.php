<?php
session_start();
session_destroy();
setcookie("user_logged", "", time() - 3600, "/");
setcookie("matricule", "", time() - 3600, "/");
header("Location: login.php");
exit();

?>