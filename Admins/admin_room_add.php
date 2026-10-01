<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "dbconnection.php";

$msg = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_name = trim($_POST['room_name']);
    $location = trim($_POST['location']);
    $room_type = trim($_POST['room_type']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);

    if (empty($room_name) || empty($location) || empty($room_type)) {
        $msg = "Room name, location, and type are required.";
        $type = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO rooms (room_name, location, room_type, price, description, availability) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdds", $room_name, $location, $room_type, $price, $description, $availability);

        if ($stmt->execute()) {
            $msg = "Room added successfully.";
            $type = "success";
        } else {
            $msg = "Failed to add room.";
            $type = "danger";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2b2b2b;
            font-family: Arial, sans-serif;
            color: #e0e0e0;
        }
        .navbar {
            background-color: #1f1f1f;
            padding: 15px;
            border-bottom: 1px solid #444;
        }
        .navbar-brand {
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
        }
        .form-box {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
        }
        .card {
            background-color: #363636;
            border: none;
            border-radius: 8px;
            padding: 20px;
        }
        .form-label {
            font-size: 13px;
            color: #ccc;
        }
        .form-control, .form-select {
            background-color: #444;
            border: 1px solid #555;
            color: #fff;
            font-size: 13px;
        }
        .form-control:focus, .form-select:focus {
            background-color: #444;
            border-color: #666;
            color: #fff;
            box-shadow: none;
        }
        .btn-primary {
            background-color: #4a5568;
            border: none;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #556677;
        }
        .btn-back {
            background-color: #555;
            color: #fff;
            border: none;
            padding: 6px 15px;
            font-size: 13px;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-back:hover { background-color: #666; color: #fff; }
        h5 { color: #fff; margin-bottom: 20px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="admin.php">Admin Panel</a>
        <a href="admin.php" class="btn-back">Back</a>
    </div>
</nav>

<div class="form-box">
    <div class="card shadow-sm">
        <h5>Add New Room</h5>

        <?php if ($msg != "") { ?>
            <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <form method="POST" action="admin_room_add.php">
            <div class="mb-3">
                <label class="form-label">Room Name</label>
                <input type="text" class="form-control" name="room_name" placeholder="Enter room name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" placeholder="Enter location" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Room Type</label>
                <select class="form-select" name="room_type" required>
                    <option value="">Select type</option>
                    <option value="Studio">Studio</option>
                    <option value="Suite">Suite</option>
                    <option value="Shared">Shared</option>
                    <option value="Conference">Conference</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Price per night</label>
                <input type="number" step="0.01" class="form-control" name="price" placeholder="0.00">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3" placeholder="Enter description"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Availability</label>
                <select class="form-select" name="availability">
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Add Room</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>