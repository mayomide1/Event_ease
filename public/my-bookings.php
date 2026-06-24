<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

// If user is an organizer, redirect to dashboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'organizer') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

// Fetch bookings for this user with event details
$stmt = $db->prepare("
    SELECT 
        b.*, 
        e.title as event_title, 
        e.start_date, 
        e.end_date, 
        e.venue, 
        e.city, 
        e.image,
        e.description
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$my_bookings = $stmt->fetchAll();

// Helper function to format date/time
function formatBookingDate($datetime) {
    return date('F j, Y', strtotime($datetime));
}
function formatBookingTime($start, $end) {
    return date('g:i A', strtotime($start)) . ' - ' . date('g:i A', strtotime($end));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - EventEase</title>
    <!-- Layout CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/my-bookings.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Hamburger Button -->
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-ticket-alt"></i> My Bookings</h1>
                <p>View and manage all your event bookings</p>
            </div>

            <!-- Bookings Section -->
            <div class="bookings-section">
                <?php if (count($my_bookings) > 0): ?>
                    <div class="bookings-grid">
                        <?php foreach ($my_bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-image">
                                    <img src="assets/images/events/<?php echo htmlspecialchars($booking['image'] ?? 'placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($booking['event_title']); ?>"
                                         onerror="this.src='assets/images/event-placeholder.jpg'">
                                    <span class="booking-status <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                <div class="booking-body">
                                    <h3><?php echo htmlspecialchars($booking['event_title']); ?></h3>
                                    <div class="booking-details">
                                        <div class="detail-row">
                                            <i class="fas fa-calendar-day"></i>
                                            <span><?php echo formatBookingDate($booking['start_date']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo formatBookingTime($booking['start_date'], $booking['end_date']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($booking['venue'] . ', ' . $booking['city']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-ticket-alt"></i>
                                            <span><?php echo $booking['ticket_quantity']; ?> tickets</span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span><?php echo $booking['total_amount'] == 0 ? 'Free' : '₦' . number_format($booking['total_amount'], 2); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-barcode"></i>
                                            <span><strong>Ref:</strong> <?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                                        </div>
                                    </div>
                                    <div class="booking-actions">
                                        <a href="event-details.php?id=<?php echo $booking['event_id']; ?>" class="btn-view-event">
                                            <i class="fas fa-eye"></i> View Event
                                        </a>
                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                            <a href="download-ticket.php?ref=<?php echo $booking['booking_reference']; ?>" class="btn-download">
                                                <i class="fas fa-download"></i> Download Ticket
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-bookings">
                        <i class="fas fa-ticket-alt" style="font-size: 64px; color: #ccc;"></i>
                        <h3>No Bookings Yet</h3>
                        <p>You haven't booked any events yet. Start exploring!</p>
                        <a href="events.php" class="btn-primary"><i class="fas fa-search"></i> Browse Events</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Same hamburger script as dashboard
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');

            if (!sidebar || !hamburgerBtn || !overlay) {
                console.error('Required elements not found!');
                return;
            }

            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                const icon = hamburgerBtn.querySelector('i');
                if (sidebar.classList.contains('active')) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            }

            hamburgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });

            overlay.addEventListener('click', function() {
                if (sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            });

            mainContent.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            });
        });
    </script>
</body>
</html>