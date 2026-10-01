<?php

$conn = new mysqli("localhost", "root", "", "room_booking_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>