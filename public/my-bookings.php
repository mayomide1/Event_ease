<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

// Check if user is an organizer (redirect if they are)
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'organizer') {
    header('Location: dashboard.php');
    exit();
}

// Sample bookings data (will be replaced with database later)
$my_bookings = [
    [
        'id' => 1,
        'event_title' => 'Tech Conference 2026',
        'event_date' => 'June 20, 2026',
        'event_time' => '9:00 AM - 6:00 PM',
        'venue' => 'Lagos Convention Center',
        'city' => 'Lagos',
        'tickets' => 2,
        'total_amount' => '₦30,000',
        'status' => 'confirmed',
        'booking_date' => 'June 1, 2026',
        'ticket_code' => 'TCK-2026-001',
        'image' => 'tech-conference.jpg'
    ],
    [
        'id' => 2,
        'event_title' => 'Music Festival 2026',
        'event_date' => 'July 15, 2026',
        'event_time' => '4:00 PM - 11:59 PM',
        'venue' => 'Eko Atlantic City',
        'city' => 'Lagos',
        'tickets' => 3,
        'total_amount' => '₦75,000',
        'status' => 'pending',
        'booking_date' => 'June 5, 2026',
        'ticket_code' => 'TCK-2026-002',
        'image' => 'music-festival.jpg'
    ],
    [
        'id' => 3,
        'event_title' => 'Entrepreneurship Workshop',
        'event_date' => 'August 5, 2026',
        'event_time' => '10:00 AM - 4:00 PM',
        'venue' => 'Business Hub',
        'city' => 'Abuja',
        'tickets' => 1,
        'total_amount' => '₦10,000',
        'status' => 'cancelled',
        'booking_date' => 'June 10, 2026',
        'ticket_code' => 'TCK-2026-003',
        'image' => 'workshop.jpg'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - EventEase</title>
    <!-- Layout CSS (dashboard) -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <!-- Specific bookings styles -->
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
            <!-- Header (matches dashboard) -->
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
                                    <img src="assets/images/events/<?php echo $booking['image']; ?>" 
                                         alt="<?php echo $booking['event_title']; ?>"
                                         onerror="this.src='assets/images/event-placeholder.jpg'">
                                    <span class="booking-status <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                <div class="booking-body">
                                    <h3><?php echo $booking['event_title']; ?></h3>
                                    <div class="booking-details">
                                        <div class="detail-row">
                                            <i class="fas fa-calendar-day"></i>
                                            <span><?php echo $booking['event_date']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $booking['event_time']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo $booking['venue'] . ', ' . $booking['city']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-ticket-alt"></i>
                                            <span><?php echo $booking['tickets']; ?> tickets</span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span><?php echo $booking['total_amount']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <i class="fas fa-barcode"></i>
                                            <span><strong>Code:</strong> <?php echo $booking['ticket_code']; ?></span>
                                        </div>
                                    </div>
                                    <div class="booking-actions">
                                        <a href="event-details.php?id=<?php echo $booking['id']; ?>" class="btn-view-event">
                                            <i class="fas fa-eye"></i> View Event
                                        </a>
                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                            <a href="download-ticket.php?id=<?php echo $booking['id']; ?>" class="btn-download">
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
                        <a href="browse.php" class="btn-primary"><i class="fas fa-search"></i> Browse Events</a>
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