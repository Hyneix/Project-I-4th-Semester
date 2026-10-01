<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "dbconnection.php";

$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($room_id > 0) {
    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin.php?msg=Room removed");
exit();
?>