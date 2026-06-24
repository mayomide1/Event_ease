<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is an organizer/admin
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}
if (!in_array($_SESSION['user_role'], ['organizer', 'admin'])) {
    $_SESSION['error'] = "Access denied.";
    header('Location: browse.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id <= 0) {
    $_SESSION['error'] = "Invalid event ID.";
    header('Location: my-events.php');
    exit();
}

// Verify that the event belongs to this organizer
$stmt = $db->prepare("SELECT id, image FROM events WHERE id = ? AND organizer_id = ?");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch();

if (!$event) {
    $_SESSION['error'] = "Event not found or you don't have permission to delete it.";
    header('Location: my-events.php');
    exit();
}

// Optional: Delete the associated image file from the server
if ($event['image']) {
    $image_path = __DIR__ . '/assets/images/events/' . $event['image'];
    if (file_exists($image_path)) {
        unlink($image_path); // Remove the file
    }
}

// Delete the event (bookings will be cascaded due to foreign key)
$delete = $db->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
if ($delete->execute([$event_id, $user_id])) {
    $_SESSION['success'] = "Event deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete event.";
}

header('Location: my-events.php');
exit();