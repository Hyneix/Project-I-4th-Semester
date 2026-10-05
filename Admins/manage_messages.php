<?php
include "admin_header.php";


$show = 10;
if (isset($_GET['show'])) {
    $show = (int) $_GET['show'];
}
if (isset($_POST['show'])) {
    $show = (int) $_POST['show'];
}
if ($show < 10) {
    $show = 10;
}

if (isset($_POST['delete_message'])) {
    $message_id = (int) $_POST['message_id'];

    $stmt = mysqli_prepare($conn, "DELETE FROM contact_messages WHERE message_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $message_id);
    mysqli_stmt_execute($stmt);

    header("Location: manage_messages.php?show=" . $show . "&msg=" . urlencode("Message deleted."));
    exit();
}

$result = mysqli_query($conn, "SELECT COUNT(*) FROM contact_messages");
$total_messages = mysqli_fetch_row($result)[0];

$stmt = mysqli_prepare($conn, "SELECT * FROM contact_messages ORDER BY message_id DESC LIMIT ?");
mysqli_stmt_bind_param($stmt, "i", $show);
mysqli_stmt_execute($stmt);
$messages = mysqli_stmt_get_result($stmt);
?>

<div class="page-top">
    <h1 class="page-title">Messages <span class="text-muted" style="font-size:15px; font-weight:normal;">
        (showing <?php echo min($show, $total_messages); ?> of <?php echo $total_messages; ?>)</span></h1>
</div>

<div class="table-box">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>From</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>

        <?php if (mysqli_num_rows($messages) > 0) { ?>
            <?php while ($message = mysqli_fetch_assoc($messages)) { ?>
            <tr>
                <td><?php echo (int) $message['message_id']; ?></td>

                <td>
                    <?php echo htmlspecialchars($message['full_name']); ?><br>
                    <span class="text-muted"><?php echo htmlspecialchars($message['email']); ?></span><br>
                    <span class="text-muted" style="font-size:12px;">
                        <?php echo ($message['user_id'] != null) ? 'Registered user' : 'Guest'; ?>
                    </span>
                </td>

                <td><?php echo htmlspecialchars($message['subject']); ?></td>

                <td style="min-width:260px; max-width:420px;">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </td>

                <td class="text-nowrap"><?php echo date('d M Y, h:i A', strtotime($message['created_at'])); ?></td>

                <td class="text-nowrap">
                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=<?php echo rawurlencode('Re: ' . $message['subject']); ?>"
                       class="btn btn-outline-dark btn-sm">Reply</a>

                    <form method="POST" action="manage_messages.php" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this message?');">
                        <input type="hidden" name="message_id" value="<?php echo (int) $message['message_id']; ?>">
                        <input type="hidden" name="show" value="<?php echo $show; ?>">
                        <button type="submit" name="delete_message" class="btn btn-dark btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="6" class="text-center text-muted">No messages yet.</td></tr>
        <?php } ?>

        </tbody>
    </table>
</div>

<?php if ($total_messages > $show) { ?>
    <div id="more" class="text-center mt-4">
        <a href="manage_messages.php?show=<?php echo $show + 10; ?>#more" class="btn btn-dark px-4">See More</a>
    </div>
<?php } ?>

</div>

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>