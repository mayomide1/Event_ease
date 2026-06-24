<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

// Ensure only organizers/admins can access
if (!in_array($_SESSION['user_role'], ['organizer', 'admin'])) {
    $_SESSION['error'] = "Access denied.";
    header('Location: browse.php');
    exit();
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($booking_id <= 0) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: bookings.php');
    exit();
}

$user_id = $_SESSION['user_id'];
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

if (!$booking || $booking['organizer_id'] != $user_id) {
    $_SESSION['error'] = "You do not have permission to delete this booking.";
    header('Location: bookings.php');
    exit();
}

// Delete the booking
$delete = $db->prepare("DELETE FROM bookings WHERE id = ?");
if ($delete->execute([$booking_id])) {
    $_SESSION['success'] = "Booking deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete booking.";
}

header('Location: bookings.php');
exit();