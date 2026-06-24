<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

// Dummy bookings data (will be replaced with database later)
$bookings = [
    [
        'id' => 1,
        'event_title' => 'Tech Conference 2026',
        'event_date' => 'June 20, 2026',
        'tickets' => 3,
        'total_amount' => '₦45,000',
        'status' => 'confirmed',
        'booking_date' => 'June 1, 2026',
        'customer' => 'John Doe',
        'customer_email' => 'john@example.com',
        'payment_method' => 'Card'
    ],
    [
        'id' => 2,
        'event_title' => 'Music Festival 2026',
        'event_date' => 'July 15, 2026',
        'tickets' => 2,
        'total_amount' => '₦50,000',
        'status' => 'pending',
        'booking_date' => 'June 5, 2026',
        'customer' => 'Jane Smith',
        'customer_email' => 'jane@example.com',
        'payment_method' => 'Bank Transfer'
    ],
    [
        'id' => 3,
        'event_title' => 'Entrepreneurship Workshop',
        'event_date' => 'August 5, 2026',
        'tickets' => 1,
        'total_amount' => '₦10,000',
        'status' => 'cancelled',
        'booking_date' => 'June 10, 2026',
        'customer' => 'Samuel Ade',
        'customer_email' => 'sam@example.com',
        'payment_method' => 'Paystack'
    ],
    [
        'id' => 4,
        'event_title' => 'Charity Gala Night',
        'event_date' => 'September 10, 2026',
        'tickets' => 5,
        'total_amount' => '₦0',
        'status' => 'confirmed',
        'booking_date' => 'June 15, 2026',
        'customer' => 'Chioma Okafor',
        'customer_email' => 'chioma@example.com',
        'payment_method' => 'Free'
    ],
    [
        'id' => 5,
        'event_title' => 'Sports Tournament',
        'event_date' => 'October 2, 2026',
        'tickets' => 10,
        'total_amount' => '₦50,000',
        'status' => 'completed',
        'booking_date' => 'June 20, 2026',
        'customer' => 'Kunle Bakare',
        'customer_email' => 'kunle@example.com',
        'payment_method' => 'Card'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/bookings.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="header">
                <h1>Bookings</h1>
                <p><i class="fa-regular fa-calendar-check"></i> Manage all bookings for your events</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p><?php echo count($bookings); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Confirmed</h3>
                    <p><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <p><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'pending')); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Revenue</h3>
                    <p>₦155,000</p>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="recent-events">
                <h2><i class="fas fa-ticket-alt"></i> All Bookings</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Event</th>
                                <th>Customer</th>
                                <th>Tickets</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $index => $booking): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo $booking['event_title']; ?></strong><br>
                                    <small><?php echo $booking['event_date']; ?></small>
                                </td>
                                <td>
                                    <?php echo $booking['customer']; ?><br>
                                    <small><?php echo $booking['customer_email']; ?></small>
                                </td>
                                <td><?php echo $booking['tickets']; ?></td>
                                <td><?php echo $booking['total_amount']; ?></td>
                                <td><?php echo $booking['booking_date']; ?></td>
                                <td>
                                    <span class="status <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="btn-small">View</a>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <a href="#" class="btn-small btn-confirm">Confirm</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');

            // Ensure elements exist
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