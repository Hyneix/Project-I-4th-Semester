<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "dbconnection.php";

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

$upload_msg = "";

/* Get account creation date */
$sql = "SELECT created_at FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$account_created = date("m/d/Y", strtotime($user['created_at']));

$stmt->close();


/* Profile picture upload */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_pic'])) {

    $upload_folder = "uploads/";

    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0777, true);
    }

    $file = $upload_folder . "user_" . $user_id . ".jpg";

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file)) {
        $upload_msg = "Photo updated!";
    } else {
        $upload_msg = "Upload failed.";
    }
}

$profile_pic = "uploads/user_" . $user_id . ".jpg";
$has_profile_pic = file_exists($profile_pic);


/* Upcoming bookings */
$sql = "SELECT purpose, room_id, booking_date
        FROM bookings
        WHERE user_id = ?
        AND booking_date >= CURDATE()
        AND booking_status = 'confirmed'
        ORDER BY booking_date ASC, start_time ASC
        LIMIT 4";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$upcoming_result = $stmt->get_result();

$stmt->close();


/* Booking history */
$sql = "SELECT purpose, room_id, booking_date
        FROM bookings
        WHERE user_id = ?
        AND booking_date < CURDATE()
        ORDER BY booking_date DESC
        LIMIT 5";

$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();

$history_result = $stmt2->get_result();

$stmt2->close();


/* Total bookings */
$sql = "SELECT COUNT(*) AS total
        FROM bookings
        WHERE user_id = ?";

$stmt4 = $conn->prepare($sql);
$stmt4->bind_param("i", $user_id);
$stmt4->execute();

$count_result = $stmt4->get_result();
$total_bookings = $count_result->fetch_assoc()['total'];

$stmt4->close();


/* Convert booking date to simple text */
function timeAgo($date)
{
    $difference = time() - strtotime($date);

    if ($difference < 86400) {
        return "Today";
    }

    if ($difference < 604800) {
        return floor($difference / 86400) . " days ago";
    }

    if ($difference < 2592000) {
        return floor($difference / 604800) . " weeks ago";
    }

    if ($difference < 31536000) {
        return floor($difference / 2592000) . " months ago";
    }

    return floor($difference / 31536000) . " years ago";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Profile - QuickRoom</title>

    <!-- Same Bootstrap version as index.php -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ---------- Basic Page (same as index.php) ---------- */
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            color: #212529;
        }

        /* ---------- Profile cards (same box style as index.php .search-form-box) ---------- */
        .card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #212529;
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #555;
        }

        /* ---------- Profile picture ---------- */
        .profile-avatar,
        .profile-pic-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto;
        }

        .profile-avatar {
            background-color: #555;
            text-align: center;
            line-height: 100px;
            font-size: 32px;
            color: #fff;
        }

        .profile-pic-img {
            object-fit: cover;
            display: block;
        }

        .profile-name {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            margin-top: 10px;
        }

        .change-photo {
            text-align: center;
            margin-top: 8px;
        }

        .change-photo label {
            color: #888;
            font-size: 12px;
            cursor: pointer;
        }

        .change-photo label:hover {
            color: #333;
        }

        /* ---------- Info rows ---------- */
        .section-heading {
            font-size: 12px;
            color: #888;
            margin-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-label {
            color: #888;
        }

        .info-value {
            color: #333;
        }

        /* ---------- Notification box ---------- */
        .notification-box {
            background-color: #f1f3f5;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .notification-box p {
            font-size: 11px;
            color: #666;
            margin: 0;
            line-height: 1.5;
        }

        .notification-box strong {
            color: #212529;
        }

        /* ---------- Booking items ---------- */
        .booking-item {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 4px;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
        }

        .booking-date {
            color: #888;
            font-size: 12px;
        }

        /* ---------- History ---------- */
        .history-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-arrow {
            float: right;
            color: #aaa;
            font-size: 14px;
        }

        /* ---------- Footer (same as index.php) ---------- */
        footer {
            margin-top: 40px;
            padding: 20px 0;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }

        /* ---------- Dark theme (same dark-mode colors as header.php) ---------- */
        body.dark-mode {
            background: #212529;
            color: #f8f9fa;
        }

        body.dark-mode .card {
            background: #2b3035;
            border-color: #495057;
            color: #e0e0e0;
        }

        body.dark-mode .card-title {
            color: #ccc;
        }

        body.dark-mode .section-heading,
        body.dark-mode .info-label {
            color: #adb5bd;
        }

        body.dark-mode .info-value {
            color: #e0e0e0;
        }

        body.dark-mode .change-photo label {
            color: #aaa;
        }

        body.dark-mode .change-photo label:hover {
            color: #fff;
        }

        body.dark-mode .notification-box {
            background-color: #343a40;
        }

        body.dark-mode .notification-box p {
            color: #adb5bd;
        }

        body.dark-mode .notification-box strong {
            color: #fff;
        }

        body.dark-mode .booking-item {
            background-color: #343a40;
            border-color: #495057;
        }

        body.dark-mode .booking-date {
            color: #adb5bd;
        }

        body.dark-mode .history-item,
        body.dark-mode .booking-list-item {
            border-color: #495057;
        }

        body.dark-mode .empty-msg {
            color: #adb5bd;
        }

        body.dark-mode footer {
            color: #adb5bd;
        }
    </style>
</head>

<body>
<?php include "header.php"; ?>


<div class="container py-4">

    <div class="row">

        <!-- Left Sidebar -->
        <div class="col-md-3">

            <div class="card">

                <div class="card-body text-center">

                    <?php if ($has_profile_pic) { ?>

                        <img src="<?php echo $profile_pic; ?>?t=<?php echo time(); ?>"
                             class="profile-pic-img"
                             alt="Profile">

                    <?php } else { ?>

                        <div class="profile-avatar">
                            <?php
                            $name_parts = explode(" ", $user_name);
                            echo strtoupper(substr($name_parts[0], 0, 1));
                            ?>
                        </div>

                    <?php } ?>

                    <div class="change-photo">

                        <form method="POST" enctype="multipart/form-data">

                            <input type="file"
                                   name="profile_pic"
                                   id="profile_pic"
                                   style="display:none;"
                                   onchange="this.form.submit();">

                            <label for="profile_pic">
                                &#128247; Change Photo
                            </label>

                        </form>

                        <?php if ($upload_msg != "") { ?>

                            <small class="text-success">
                                <?php echo $upload_msg; ?>
                            </small>

                        <?php } ?>

                    </div>

                    <div class="profile-name">
                        <?php echo htmlspecialchars($user_name); ?>
                    </div>

                    <div class="text-start" style="margin-top:20px;">

                        <p class="section-heading">
                            Account info:
                        </p>

                        <div class="info-row">

                            <span class="info-label">
                                Account Created
                            </span>

                            <span class="info-value">
                                <?php echo $account_created; ?>
                            </span>

                        </div>

                        <div class="info-row">

                            <span class="info-label">
                                User ID
                            </span>

                            <span class="info-value">
                                <?php echo $user_id; ?>
                            </span>

                        </div>

                    </div>

                    <div class="notification-box text-start">

                        <p>
                            <strong>
                                Welcome back!
                            </strong>
                        </p>

                        <p style="margin-top:5px;">
                            You have <?php echo $total_bookings; ?> total
                            booking(s). Manage your room bookings from this panel.
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- Center Content -->
        <div class="col-md-9">

            <!-- Upcoming Bookings -->
            <div class="card">

                <div class="card-body">

                    <h6 class="card-title">
                        Upcoming bookings
                    </h6>

                    <?php if ($upcoming_result->num_rows > 0) { ?>

                        <?php while ($row = $upcoming_result->fetch_assoc()) { ?>

                            <?php
                            if (!empty($row['purpose'])) {
                                $display_name = $row['purpose'];
                            } else {
                                $display_name = "Room #" . $row['room_id'];
                            }

                            $date = date(
                                "M j",
                                strtotime($row['booking_date'])
                            );
                            ?>

                            <div class="booking-item">

                                <span>
                                    &#128205;
                                    <?php echo htmlspecialchars($display_name); ?>
                                </span>

                                <span class="booking-date">
                                    <?php echo $date; ?>
                                </span>

                            </div>

                        <?php } ?>

                    <?php } else { ?>

                        <div class="empty-msg">
                            No upcoming bookings
                        </div>

                    <?php } ?>

                </div>

            </div>


            <!-- Booking History -->
            <div class="card">

                <div class="card-body">

                    <h6 class="card-title">
                        Booking history
                    </h6>

                    <?php if ($history_result->num_rows > 0) { ?>

                        <?php while ($row = $history_result->fetch_assoc()) { ?>

                            <?php
                            if (!empty($row['purpose'])) {
                                $display_name = $row['purpose'];
                            } else {
                                $display_name = "Room #" . $row['room_id'];
                            }

                            $ago = timeAgo($row['booking_date']);
                            ?>

                            <div class="history-item">

                                <span>
                                    &#128336;
                                    <?php echo htmlspecialchars($display_name); ?>
                                </span>

                                <span class="history-arrow">
                                    &#8250;
                                </span>

                                <br>

                                <small class="text-muted">
                                    <?php echo $ago; ?>
                                </small>

                            </div>

                        <?php } ?>

                    <?php } else { ?>

                        <div class="empty-msg">
                            No booking history
                        </div>

                    <?php } ?>

                </div>

            </div>

        </div>




    </div>

</div>

<?php include "Footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>