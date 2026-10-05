<?php

session_start();
include "dbconnection.php";

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

$error = "";
$success = "";

$check_in = "";
$check_out = "";
$guests = "";
$purpose = "";


$sql = "SELECT * FROM rooms WHERE room_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();

$result = $stmt->get_result();
$room = $result->fetch_assoc();


$logged_in = isset($_SESSION['user_id']);


if (isset($_GET['success'])) {
    $success = "Booking submitted successfully. Waiting for admin approval.";
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $logged_in) {

    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = (int)$_POST['guests'];
    $purpose = trim($_POST['purpose']);

    $today = date("Y-m-d");




    if ($check_in == "" || $check_out == "" || $guests < 1) {

        $error = "Please fill all required fields.";
    } elseif ($check_in < $today) {

        $error = "Check-in date cannot be in the past.";
    } elseif ($check_out <= $check_in) {

        $error = "Check-out must be after check-in.";
    } elseif ($guests > $room['capacity']) {

        $error = "This room allows only "
            . $room['capacity']
            . " guests.";
    } elseif (strtolower($room['status']) != "available") {

        $error = "This room is currently unavailable.";
    } else {



        $sql = "SELECT booking_id
                FROM bookings
                WHERE room_id = ?
                AND booking_status IN ('Pending', 'Approved')
                AND check_in_date < ?
                AND check_out_date > ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iss",
            $room_id,
            $check_out,
            $check_in
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows > 0) {

            $error = "Sorry, this room is already booked for these dates.";
        } else {



            $nights = (
                strtotime($check_out) -
                strtotime($check_in)
            ) / 86400;

            $price = $room['price_per_night'];

            $total_price = $nights * $price;


            if ($purpose == "") {
                $purpose = $room['room_name'];
            }




            $user_id = $_SESSION['user_id'];

            $start_time = "14:00:00";
            $end_time = "11:00:00";

            $sql = "INSERT INTO bookings
                    (
                        user_id,
                        room_id,
                        booking_date,
                        start_time,
                        end_time,
                        purpose,
                        booking_status,
                        check_in_date,
                        check_out_date,
                        guests,
                        total_price
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "iissssssid",
                $user_id,
                $room_id,
                $check_in,
                $start_time,
                $end_time,
                $purpose,
                $check_in,
                $check_out,
                $guests,
                $total_price
            );


            if ($stmt->execute()) {

                header(
                    "Location: Booking.php?room_id="
                        . $room_id
                        . "&success=1"
                );

                exit();
            } else {

                $error = "Booking failed. Please try again.";
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Book Room</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1000px;
        }

        .card {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .room-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
        }
    </style>

</head>


<body>


    <?php include "header.php"; ?>


    <div class="container py-4">


        <?php if (!$room) { ?>

            <div class="alert alert-danger">
                Room not found.
            </div>

        <?php } else { ?>


            <h2 class="mb-4">
                Book <?php echo htmlspecialchars($room['room_name']); ?>
            </h2>


            <?php if ($success != "") { ?>

                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>

            <?php } ?>


            <?php if ($error != "") { ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php } ?>


            <?php if (!$logged_in) { ?>

                <div class="card p-4">

                    <h5>
                        Please login to book this room.
                    </h5>

                    <a href="login.php" class="btn btn-dark">
                        Login
                    </a>

                </div>


            <?php } else { ?>

                <div class="row">

                    <div class="col-md-7">

                        <div class="card p-4">

                            <h5 class="mb-3">
                                Booking Details
                            </h5>

                            <form method="POST" onsubmit="return checkForm()">

                                <label>
                                    Check-in Date
                                </label>

                                <input type="date" name="check_in" id="check_in" class="form-control mb-3" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($check_in); ?>" onchange="calculatePrice()" required>


                                <label>
                                    Check-out Date
                                </label>

                                <input type="date" name="check_out" id="check_out" class="form-control mb-3" value="<?php echo htmlspecialchars($check_out); ?>" onchange="calculatePrice()" required>


                                <label>
                                    Number of Guests
                                </label>

                                <input type="number" name="guests" id="guests" class="form-control mb-3" min="1" max="<?php echo $room['capacity']; ?>" value="<?php echo htmlspecialchars($guests); ?>" required>


                                <label>
                                    Purpose / Note
                                </label>

                                <input
                                    type="text"
                                    name="purpose"
                                    class="form-control mb-3"
                                    value="<?php echo htmlspecialchars($purpose); ?>"
                                    placeholder="Optional">


                                <div class="border-top pt-3">

                                    <p>
                                        Price per night:
                                        <strong>
                                            Rs.
                                            <?php
                                            echo number_format(
                                                $room['price_per_night']
                                            );
                                            ?>
                                        </strong>
                                    </p>


                                    <p>
                                        Nights:
                                        <strong id="nights">
                                            0
                                        </strong>
                                    </p>


                                    <p class="total">
                                        Total:
                                        <span id="total">
                                            Rs. 0
                                        </span>
                                    </p>

                                </div>


                                <button type="submit" class="btn btn-primary w-100">
                                    Book Now
                                </button>


                            </form>

                        </div>

                    </div>

                    <div class="col-md-5">

                        <div class="card">

                            <?php if ($room['image'] != "") { ?>

                                <img
                                    src="images/<?php echo htmlspecialchars($room['image']); ?>"
                                    class="room-image"
                                    alt="Room">

                            <?php } ?>


                            <div class="card-body">

                                <h5>
                                    <?php
                                    echo htmlspecialchars(
                                        $room['room_name']
                                    );
                                    ?>
                                </h5>


                                <p>
                                    Type:
                                    <?php
                                    echo htmlspecialchars(
                                        $room['room_type']
                                    );
                                    ?>
                                </p>


                                <p>
                                    Location:
                                    <?php
                                    echo htmlspecialchars(
                                        $room['location']
                                    );
                                    ?>
                                </p>


                                <p>
                                    Capacity:
                                    <?php
                                    echo $room['capacity'];
                                    ?>
                                    guests
                                </p>


                                <p>
                                    Price:
                                    Rs.
                                    <?php
                                    echo number_format(
                                        $room['price_per_night']
                                    );
                                    ?>
                                    / night
                                </p>


                            </div>

                        </div>

                    </div>


                </div>


            <?php } ?>


        <?php } ?>


    </div>


    <?php include "Footer.php"; ?>


    <script>
        function calculatePrice() {

            var checkIn =
                document.getElementById("check_in").value;

            var checkOut =
                document.getElementById("check_out").value;


            if (checkIn == "" || checkOut == "") {
                return;
            }


            var start =
                new Date(checkIn);

            var end =
                new Date(checkOut);


            var nights =
                (end - start) /
                (1000 * 60 * 60 * 24);


            if (nights > 0) {

                var price =
                    <?php echo $room ? $room['price_per_night'] : 0; ?>;

                var total =
                    nights * price;


                document.getElementById("nights").innerText =
                    nights;

                document.getElementById("total").innerText =
                    "Rs. " + total.toLocaleString();

            } else {

                document.getElementById("nights").innerText =
                    "0";

                document.getElementById("total").innerText =
                    "Rs. 0";
            }
        }


        function checkForm() {

            var checkIn =
                document.getElementById("check_in").value;

            var checkOut =
                document.getElementById("check_out").value;


            if (checkOut <= checkIn) {

                alert(
                    "Check-out must be after check-in."
                );

                return false;
            }


            return true;
        }


        calculatePrice();
    </script>


</body>

</html>