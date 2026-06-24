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

// Sample tickets data (will be replaced with database later)
// Only show confirmed tickets
$my_tickets = [
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
        'ticket_code' => 'TCK-2026-001',
        'qr_code' => 'qr-tck-001.png',
        'image' => 'tech-conference.jpg',
        'seat_number' => 'A12, A13',
        'attendee_name' => $_SESSION['user_name'] ?? 'John Doe'
    ],
    [
        'id' => 4,
        'event_title' => 'Charity Gala Night',
        'event_date' => 'September 10, 2026',
        'event_time' => '6:00 PM - 10:00 PM',
        'venue' => 'Grand Ballroom',
        'city' => 'Port Harcourt',
        'tickets' => 4,
        'total_amount' => 'Free',
        'status' => 'confirmed',
        'ticket_code' => 'TCK-2026-004',
        'qr_code' => 'qr-tck-004.png',
        'image' => 'charity-gala.jpg',
        'seat_number' => 'B5, B6, B7, B8',
        'attendee_name' => $_SESSION['user_name'] ?? 'John Doe'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/my-tickets.css">
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
                <h1><i class="fas fa-qrcode"></i> My Tickets</h1>
                <p>Your valid entry passes for upcoming events</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Tickets</h3>
                    <p><?php echo count($my_tickets); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Upcoming Events</h3>
                    <p><?php echo count($my_tickets); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Tickets Purchased</h3>
                    <p><?php echo array_sum(array_column($my_tickets, 'tickets')); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Spent</h3>
                    <p>₦<?php 
                        $total = 0;
                        foreach ($my_tickets as $ticket) {
                            if ($ticket['total_amount'] != 'Free') {
                                $total += (int)str_replace(['₦', ','], '', $ticket['total_amount']);
                            }
                        }
                        echo number_format($total, 0);
                    ?></p>
                </div>
            </div>

            <!-- Tickets Section -->
            <div class="tickets-section">
                <?php if (count($my_tickets) > 0): ?>
                    <div class="tickets-grid">
                        <?php foreach ($my_tickets as $ticket): ?>
                            <div class="ticket-card">
                                <!-- Ticket Header -->
                                <div class="ticket-header">
                                    <div class="ticket-header-left">
                                        <span class="ticket-status confirmed">
                                            <i class="fas fa-check-circle"></i> Confirmed
                                        </span>
                                        <span class="ticket-code">#<?php echo $ticket['ticket_code']; ?></span>
                                    </div>
                                    <div class="ticket-header-right">
                                        <span class="ticket-date"><?php echo $ticket['event_date']; ?></span>
                                    </div>
                                </div>

                                <!-- Ticket Body -->
                                <div class="ticket-body">
                                    <div class="ticket-left">
                                        <div class="ticket-qr">
                                            <!-- QR Code placeholder - replace with actual QR code generation -->
                                            <div class="qr-placeholder">
                                                <i class="fas fa-qrcode" style="font-size: 80px; color: #333;"></i>
                                            </div>
                                            <span class="qr-label">Scan for entry</span>
                                        </div>
                                    </div>
                                    <div class="ticket-right">
                                        <h3><?php echo $ticket['event_title']; ?></h3>
                                        <div class="ticket-meta">
                                            <div class="meta-row">
                                                <i class="fas fa-calendar-day"></i>
                                                <span><?php echo $ticket['event_date']; ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo $ticket['event_time']; ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo $ticket['venue'] . ', ' . $ticket['city']; ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-user"></i>
                                                <span><?php echo $ticket['attendee_name']; ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-chair"></i>
                                                <span>Seat: <?php echo $ticket['seat_number']; ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-ticket-alt"></i>
                                                <span><?php echo $ticket['tickets']; ?> tickets</span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span><?php echo $ticket['total_amount']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ticket Footer -->
                                <div class="ticket-footer">
                                    <div class="ticket-actions">
                                        <a href="download-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn-download">
                                            <i class="fas fa-download"></i> Download Ticket
                                        </a>
                                        <button class="btn-print" onclick="window.print()">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-tickets">
                        <i class="fas fa-ticket-alt" style="font-size: 64px; color: #ccc;"></i>
                        <h3>No Tickets Yet</h3>
                        <p>You don't have any confirmed tickets. Book an event to get your tickets!</p>
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