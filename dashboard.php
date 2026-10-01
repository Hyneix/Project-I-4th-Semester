<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Room Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .dashboard-box {
            width: 100%;
            max-width: 500px;
            margin: 80px auto;
            padding: 20px;
        }
        .card {
            border: none;
            padding: 30px;
            text-align: center;
        }
        h3 {
            margin-bottom: 15px;
            font-size: 24px;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        a {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="dashboard-box">
    <div class="card shadow-sm">
        <h3>Welcome, <?php echo $_SESSION['full_name']; ?></h3>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>