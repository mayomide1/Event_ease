<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if ($booking_id <= 0) {
    $_SESSION['error'] = "Invalid booking.";
    header('Location: browse.php');
    exit();
}

$db = Database::getConnection();

// Fetch booking details
$stmt = $db->prepare("
    SELECT b.*, e.title, e.start_date, e.venue, e.city 
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = "Booking not found.";
    header('Location: browse.php');
    exit();
}

// If the booking is already confirmed, redirect to success
if ($booking['status'] === 'confirmed') {
    header('Location: booking-confirmation.php?ref=' . $booking['booking_reference']);
    exit();
}

// Handle payment confirmation (simulation)
$payment_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // In a real system, you would verify payment via API here
    // For demo, we simulate success
    $payment_success = true;

    if ($payment_success) {
        // Update booking status to 'confirmed'
        $update = $db->prepare("
            UPDATE bookings 
            SET status = 'confirmed', payment_status = 'paid', updated_at = NOW()
            WHERE id = ?
        ");
        $update->execute([$booking_id]);

         // Update tickets_sold
        $stmt = $db->prepare("
            UPDATE events 
            SET tickets_sold = tickets_sold + (
                SELECT ticket_quantity FROM bookings WHERE id = ?
            )
            WHERE id = (
                SELECT event_id FROM bookings WHERE id = ?
            )
        ");
        $stmt->execute([$booking_id, $booking_id]);

        $_SESSION['success'] = "Payment successful! Your booking is confirmed.";
        header('Location: booking-confirmation.php?ref=' . $booking['booking_reference']);
        exit();
    } else {
        $_SESSION['error'] = "Payment failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - EventEase</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="payment-section">
        <div class="container">
            <div class="payment-box">
                <h2>Complete Payment</h2>
                <p>Review your booking and confirm payment</p>

                <div class="booking-summary">
                    <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                    <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($booking['start_date'])); ?></p>
                    <p><strong>Venue:</strong> <?php echo htmlspecialchars($booking['venue']); ?></p>
                    <p><strong>Quantity:</strong> <?php echo $booking['ticket_quantity']; ?> tickets</p>
                    <p><strong>Total:</strong> <?php echo $booking['total_amount'] == 0 ? 'Free' : '₦' . number_format($booking['total_amount'], 2); ?></p>
                    <p><strong>Reference:</strong> <?php echo htmlspecialchars($booking['booking_reference']); ?></p>
                </div>

                <form method="POST">
                    <button type="submit" name="confirm_payment" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Confirm & Pay
                    </button>
                    <a href="event-details.php?id=<?php echo $booking['event_id']; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>