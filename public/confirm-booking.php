<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Authentication & role checks...

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($booking_id <= 0) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: bookings.php');
    exit();
}

$db = Database::getConnection();

// Verify ownership
$stmt = $db->prepare("
    SELECT b.id, b.ticket_quantity, b.event_id, e.organizer_id, e.capacity, e.tickets_sold
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking || $booking['organizer_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "You don't have permission to confirm this booking.";
    header('Location: bookings.php');
    exit();
}

// Check if enough tickets are available
$available = $booking['capacity'] - $booking['tickets_sold'];
if ($available < $booking['ticket_quantity']) {
    $_SESSION['error'] = "Not enough tickets available. Only $available left.";
    header('Location: bookings.php');
    exit();
}

// Update booking status
$update = $db->prepare("UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE id = ?");
if ($update->execute([$booking_id])) {
    // Increment tickets_sold
    $increment = $db->prepare("UPDATE events SET tickets_sold = tickets_sold + ? WHERE id = ?");
    $increment->execute([$booking['ticket_quantity'], $booking['event_id']]);
    $_SESSION['success'] = "Booking confirmed successfully.";
} else {
    $_SESSION['error'] = "Failed to confirm booking.";
}

header('Location: bookings.php');
exit();