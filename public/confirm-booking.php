<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['organizer', 'admin'])) {
    header('Location: login.php');
    exit();
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($booking_id <= 0) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: bookings.php');
    exit();
}

$db = Database::getConnection();

// Verify that this booking belongs to an event owned by this organizer
$stmt = $db->prepare("
    SELECT b.id, e.organizer_id 
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking || $booking['organizer_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "You do not have permission to confirm this booking.";
    header('Location: bookings.php');
    exit();
}

// Update booking status to 'confirmed'
$update = $db->prepare("UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE id = ?");
if ($update->execute([$booking_id])) {
    // Optionally update tickets_sold in events table
    // $db->prepare("UPDATE events SET tickets_sold = tickets_sold + (SELECT ticket_quantity FROM bookings WHERE id = ?) WHERE id = (SELECT event_id FROM bookings WHERE id = ?)")->execute([$booking_id, $booking_id]);
    $_SESSION['success'] = "Booking confirmed successfully.";
} else {
    $_SESSION['error'] = "Failed to confirm booking.";
}
header('Location: bookings.php');
exit();