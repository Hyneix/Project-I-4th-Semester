<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "dbconnection.php";

$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = "";
$type = "";

/* Get room details */
$stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();
$stmt->close();

if (!$room) {
    echo "Room not found.";
    exit();
}

/* Update room */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_name = trim($_POST['room_name']);
    $location = trim($_POST['location']);
    $room_type = trim($_POST['room_type']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);

    $stmt = $conn->prepare("UPDATE rooms SET room_name = ?, location = ?, room_type = ?, price = ?, description = ?, availability = ? WHERE room_id = ?");
    $stmt->bind_param("sssddsi", $room_name, $location, $room_type, $price, $description, $availability, $room_id);

    if ($stmt->execute()) {
        $msg = "Room updated successfully.";
        $type = "success";

        /* Refresh room data */
        $stmt2 = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
        $stmt2->bind_param("i", $room_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $room = $result2->fetch_assoc();
        $stmt2->close();
    } else {
        $msg = "Failed to update room.";
        $type = "danger";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room - Admin</title>
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
        <h5>Edit Room</h5>

        <?php if ($msg != "") { ?>
            <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <form method="POST" action="admin_room_edit.php?id=<?php echo $room_id; ?>">
            <div class="mb-3">
                <label class="form-label">Room Name</label>
                <input type="text" class="form-control" name="room_name" value="<?php echo htmlspecialchars($room['room_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($room['location']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Room Type</label>
                <select class="form-select" name="room_type" required>
                    <option value="Studio" <?php if ($room['room_type'] == 'Studio') echo 'selected'; ?>>Studio</option>
                    <option value="Suite" <?php if ($room['room_type'] == 'Suite') echo 'selected'; ?>>Suite</option>
                    <option value="Shared" <?php if ($room['room_type'] == 'Shared') echo 'selected'; ?>>Shared</option>
                    <option value="Conference" <?php if ($room['room_type'] == 'Conference') echo 'selected'; ?>>Conference</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Price per night</label>
                <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $room['price']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($room['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Availability</label>
                <select class="form-select" name="availability">
                    <option value="available" <?php if ($room['availability'] == 'available') echo 'selected'; ?>>Available</option>
                    <option value="unavailable" <?php if ($room['availability'] == 'unavailable') echo 'selected'; ?>>Unavailable</option>
                </select>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Update Room</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>