<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$reference = $_GET['ref'] ?? '';
if (empty($reference)) {
    header('Location: browse.php');
    exit();
}

$db = Database::getConnection();
$stmt = $db->prepare("
    SELECT b.*, e.title, e.start_date, e.venue, e.city 
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.booking_reference = ? AND b.user_id = ?
");
$stmt->execute([$reference, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking || $booking['status'] !== 'confirmed') {
    $_SESSION['error'] = "Booking not confirmed.";
    header('Location: browse.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - EventEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <section class="confirmation">
        <div class="container">
            <div class="success-box">
                <i class="fas fa-check-circle" style="color: #28a745; font-size: 64px;"></i>
                <h2>Booking Confirmed! 🎉</h2>
                <p>Your booking has been confirmed. Thank you!</p>
                <div class="details">
                    <p><strong>Reference:</strong> <?php echo htmlspecialchars($booking['booking_reference']); ?></p>
                    <p><strong>Event:</strong> <?php echo htmlspecialchars($booking['title']); ?></p>
                    <p><strong>Tickets:</strong> <?php echo $booking['ticket_quantity']; ?></p>
                    <p><strong>Total:</strong> <?php echo $booking['total_amount'] == 0 ? 'Free' : '₦' . number_format($booking['total_amount'], 2); ?></p>
                </div>
                <div class="actions">
                    <a href="my-tickets.php" class="btn btn-primary">View My Tickets</a>
                    <a href="download-ticket.php?ref=<?php echo $reference; ?>" class="btn btn-secondary">Download Ticket</a>
                </div>
            </div>
        </div>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>