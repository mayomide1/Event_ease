<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to book tickets.";
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = (int)($_POST['event_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $attendee_name = trim($_POST['attendee_name'] ?? '');
    $attendee_email = trim($_POST['attendee_email'] ?? '');
    $attendee_phone = trim($_POST['attendee_phone'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    // Validate inputs
    if ($event_id <= 0 || $quantity < 1) {
        $_SESSION['error'] = "Invalid event or quantity.";
        header('Location: event-details.php?id=' . $event_id);
        exit();
    }

    // Fetch event details including capacity and tickets_sold
    $stmt = $db->prepare("SELECT price, is_free, capacity, tickets_sold FROM events WHERE id = ? AND status = 'published'");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    if (!$event) {
        $_SESSION['error'] = "Event not found or unavailable.";
        header('Location: browse.php');
        exit();
    }

    // Check if enough tickets are available
    $available = $event['capacity'] - $event['tickets_sold'];
    if ($quantity > $available) {
        $_SESSION['error'] = "Sorry, only $available ticket(s) left.";
        header('Location: event-details.php?id=' . $event_id);
        exit();
    }

    // Calculate total amount
    $total_amount = $event['is_free'] ? 0 : ($event['price'] * $quantity);

    // Generate booking reference
    $reference = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // Insert booking with status 'pending'
    $stmt = $db->prepare("
        INSERT INTO bookings 
        (event_id, user_id, ticket_quantity, total_amount, booking_reference, status, payment_status, payment_method)
        VALUES (?, ?, ?, ?, ?, 'pending', 'pending', ?)
    ");
    if ($stmt->execute([$event_id, $user_id, $quantity, $total_amount, $reference, $payment_method])) {
        $booking_id = $db->lastInsertId();

        // Store booking ID in session for later confirmation
        $_SESSION['current_booking_id'] = $booking_id;

        // Redirect to payment page
        header('Location: payment.php?booking_id=' . $booking_id);
        exit();
    } else {
        $_SESSION['error'] = "Failed to create booking.";
        header('Location: event-details.php?id=' . $event_id);
        exit();
    }
}
// If not POST, redirect back
header('Location: browse.php');
exit();