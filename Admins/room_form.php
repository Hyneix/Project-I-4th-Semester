<?php
include "admin_header.php";

// room_form.php?room_id=5  -> EDIT room 5
// room_form.php            -> ADD new room
$room_id = 0;
if (isset($_GET['room_id'])) {
    $room_id = (int) $_GET['room_id'];
} elseif (isset($_POST['room_id'])) {
    $room_id = (int) $_POST['room_id'];
}
$is_edit = ($room_id > 0);

$statuses = ['Available', 'Booked', 'Maintenance'];

// Empty values for the "add" form
$room = [
    'room_name'       => '',
    'room_type'       => '',
    'capacity'        => 1,
    'price_per_night' => '',
    'location'        => '',
    'description'     => '',
    'status'          => 'Available'
];
$old_image = "";
$errors = [];

// Editing: load the room from the database
if ($is_edit) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM rooms WHERE room_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$found) {
        header("Location: manage_rooms.php?error=" . urlencode("Room not found."));
        exit();
    }

    $room = $found;
    $old_image = (string) $found['image'];
}

// Form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $room['room_name']       = trim($_POST['room_name']);
    $room['room_type']       = trim($_POST['room_type']);
    $room['capacity']        = (int) $_POST['capacity'];
    $room['price_per_night'] = trim($_POST['price_per_night']);
    $room['location']        = trim($_POST['location']);
    $room['description']     = trim($_POST['description']);
    $room['status']          = $_POST['status'];

    // Validation
    if ($room['room_name'] == '') {
        $errors[] = "Room name is required.";
    }
    if ($room['capacity'] < 1) {
        $errors[] = "Capacity must be at least 1.";
    }
    if (!is_numeric($room['price_per_night']) || $room['price_per_night'] < 0) {
        $errors[] = "Price must be a number (0 or more).";
    }
    if (!in_array($room['status'], $statuses)) {
        $errors[] = "Please choose a valid status.";
    }

    // Image upload (optional) - keep the old image unless a new one is uploaded
    $image_name = $old_image;

    if ($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
            $errors[] = "Image upload failed. Please try again.";
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $errors[] = "Only JPG, JPEG and PNG images are allowed.";
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $errors[] = "Image must be smaller than 2 MB.";
            } elseif (@getimagesize($_FILES['image']['tmp_name']) === false) {
                $errors[] = "The uploaded file is not a real image.";
            } else {
                // Unique file name so old images are not overwritten
                $new_name = "room_" . time() . "_" . rand(1000, 9999) . "." . $ext;

                if (move_uploaded_file($_FILES['image']['tmp_name'], "../images/" . $new_name)) {
                    $image_name = $new_name;
                } else {
                    $errors[] = "Could not save the image. Check that the images folder exists.";
                }
            }
        }
    }

    // Save to the database
    if (empty($errors)) {

        $price = (float) $room['price_per_night'];
        $image_value = ($image_name == "") ? null : $image_name;

        if ($is_edit) {
            $stmt = mysqli_prepare($conn, "UPDATE rooms
                                           SET room_name = ?, room_type = ?, capacity = ?, price_per_night = ?,
                                               location = ?, description = ?, image = ?, status = ?
                                           WHERE room_id = ?");
            mysqli_stmt_bind_param($stmt, "ssidssssi",
                $room['room_name'], $room['room_type'], $room['capacity'], $price,
                $room['location'], $room['description'], $image_value, $room['status'], $room_id);
            $message = "Room updated.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO rooms
                                           (room_name, room_type, capacity, price_per_night, location, description, image, status)
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssidssss",
                $room['room_name'], $room['room_type'], $room['capacity'], $price,
                $room['location'], $room['description'], $image_value, $room['status']);
            $message = "Room added.";
        }

        if (mysqli_stmt_execute($stmt)) {
            header("Location: manage_rooms.php?msg=" . urlencode($message));
            exit();
        } else {
            $errors[] = "Could not save the room. Please try again.";
        }
    }
}
?>

<div class="page-top">
    <h1 class="page-title"><?php echo $is_edit ? 'Edit Room' : 'Add Room'; ?></h1>
    <a href="manage_rooms.php" class="btn btn-outline-dark">Back</a>
</div>

<?php foreach ($errors as $error) { ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="box">
    <form method="POST" action="room_form.php" enctype="multipart/form-data">

        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label" for="room_name">Room Name</label>
                <input type="text" class="form-control" id="room_name" name="room_name"
                       value="<?php echo htmlspecialchars($room['room_name']); ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="room_type">Room Type</label>
                <input type="text" class="form-control" id="room_type" name="room_type"
                       placeholder="e.g. Deluxe"
                       value="<?php echo htmlspecialchars((string) $room['room_type']); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location"
                       placeholder="e.g. Kathmandu"
                       value="<?php echo htmlspecialchars((string) $room['location']); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="capacity">Capacity</label>
                <input type="number" class="form-control" id="capacity" name="capacity" min="1"
                       value="<?php echo (int) $room['capacity']; ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="price_per_night">Price per Night (Rs.)</label>
                <input type="number" class="form-control" id="price_per_night" name="price_per_night"
                       min="0" step="0.01"
                       value="<?php echo htmlspecialchars($room['price_per_night']); ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <?php foreach ($statuses as $status) { ?>
                        <option value="<?php echo $status; ?>" <?php if ($room['status'] == $status) echo 'selected'; ?>>
                            <?php echo $status; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars((string) $room['description']); ?></textarea>
            </div>

            <div class="col-12">
                <label class="form-label" for="image">Room Image (JPG or PNG, max 2 MB)</label>

                <?php if ($old_image != "") { ?>
                    <div class="mb-2">
                        <img src="../images/<?php echo htmlspecialchars($old_image); ?>"
                             alt="Current image" style="height:90px; border-radius:6px;">
                        <div class="text-muted" style="font-size:13px;">
                            Current image. Choose a new file only if you want to replace it.
                        </div>
                    </div>
                <?php } ?>

                <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png">
            </div>

        </div>

        <button type="submit" class="btn btn-dark mt-4">
            <?php echo $is_edit ? 'Update Room' : 'Add Room'; ?>
        </button>

    </form>
</div>

</div><!-- end .page-content -->

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>