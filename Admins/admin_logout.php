<?php
// Logs out the admin only. session_destroy() is not used,
// so a normal user in the same browser stays logged in.
session_start();

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['role_id']);

header("Location: admin_login.php");
exit();
?>