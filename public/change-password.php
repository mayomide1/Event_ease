<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

// Fetch current user data to verify old password
$stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    $_SESSION['error'] = "User not found. Please login again.";
    header('Location: login.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate current password
    if (empty($current_password)) {
        $errors[] = "Current password is required.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect.";
    }

    // Validate new password
    if (empty($new_password)) {
        $errors[] = "New password is required.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters.";
    }

    // Confirm password
    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // If no errors, update password
    if (empty($errors)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($update->execute([$hashed, $user_id])) {
            $_SESSION['success'] = "Password updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update password. Please try again.";
        }
    } else {
        // Store errors in session to display on profile page
        $_SESSION['password_errors'] = $errors;
    }

    // Redirect back to profile page
    header('Location: profile.php');
    exit();
}