<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/my-events.css">
</head>
<body>
    <!-- Mobile Menu Toggle Button -->
    <!-- <button class="sidebar-toggle" id="sidebarToggle">☰</button> -->
    
    <!-- Sidebar Overlay for Mobile -->
    <!-- <div class="sidebar-overlay" id="sidebarOverlay"></div> -->
    
    <!-- Include Sidebar Component -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>My Events</h1>
                <p>Manage all your events in one place</p>
            </div>
            <a href="create-event.php" class="btn-create">➕ Create New Event</a>
        </div>
        
        <div class="events-grid">
            <!-- Event Card 1 -->
            <div class="event-card">
                <div class="event-image">
                    <img src="assets/images/event-placeholder.jpg" alt="Event Image">
                    <span class="event-status published">Published</span>
                </div>
                <div class="event-body">
                    <h3>Tech Conference 2026</h3>
                    <p class="event-description">Join us for the biggest tech conference of the year...</p>
                    <div class="event-meta">
                        <span class="meta-item">📅 Jun 20, 2026</span>
                        <span class="meta-item">📍 Lagos</span>
                    </div>
                    <div class="event-stats">
                        <span>🎟️ 45/100 sold</span>
                        <span>💰 ₦5,000</span>
                    </div>
                    <div class="event-actions">
                        <a href="event-details.php?id=1" class="btn-view">View Details</a>
                        <a href="edit-event.php?id=1" class="btn-edit">✏️ Edit</a>
                    </div>
                </div>
            </div>
            
            <!-- Event Card 2 -->
            <div class="event-card">
                <div class="event-image">
                    <img src="assets/images/event-placeholder.jpg" alt="Event Image">
                    <span class="event-status draft">Draft</span>
                </div>
                <div class="event-body">
                    <h3>Music Festival 2026</h3>
                    <p class="event-description">A night of amazing music and entertainment...</p>
                    <div class="event-meta">
                        <span class="meta-item">📅 Jul 15, 2026</span>
                        <span class="meta-item">📍 Abuja</span>
                    </div>
                    <div class="event-stats">
                        <span>🎟️ 0/500 sold</span>
                        <span>💰 Free</span>
                    </div>
                    <div class="event-actions">
                        <a href="event-details.php?id=2" class="btn-view">View Details</a>
                        <a href="edit-event.php?id=2" class="btn-edit">✏️ Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            }
            
            toggleBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>