<?php
$conn = mysqli_connect("localhost", "root", "", "room_booking_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>