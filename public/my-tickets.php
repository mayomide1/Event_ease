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

// Fetch only confirmed bookings for this user
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
    WHERE b.user_id = ? AND b.status = 'confirmed'
    ORDER BY e.start_date ASC
");
$stmt->execute([$user_id]);
$my_tickets = $stmt->fetchAll();

// Helper functions
function formatDate($datetime) {
    return date('F j, Y', strtotime($datetime));
}
function formatTime($start, $end) {
    return date('g:i A', strtotime($start)) . ' - ' . date('g:i A', strtotime($end));
}

// Calculate stats
$total_tickets = count($my_tickets);
$total_tickets_purchased = 0;
$total_spent = 0;
foreach ($my_tickets as $ticket) {
    $total_tickets_purchased += $ticket['ticket_quantity'];
    $total_spent += (float)$ticket['total_amount'];
}
$upcoming_events = $total_tickets; // All are upcoming (you could filter by date, but for simplicity)

// Generate a dummy seat number (since we don't have seats in DB)
function generateSeat($index) {
    $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
    return $rows[$index % count($rows)] . (floor($index / count($rows)) + 1);
}
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
                    <p><?php echo $total_tickets; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Upcoming Events</h3>
                    <p><?php echo $upcoming_events; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Tickets Purchased</h3>
                    <p><?php echo $total_tickets_purchased; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Spent</h3>
                    <p>₦<?php echo number_format($total_spent, 2); ?></p>
                </div>
            </div>

            <!-- Tickets Section -->
            <div class="tickets-section">
                <?php if (count($my_tickets) > 0): ?>
                    <div class="tickets-grid">
                        <?php $seat_index = 0; ?>
                        <?php foreach ($my_tickets as $ticket): ?>
                            <?php 
                                $seat_number = generateSeat($seat_index++);
                                $ticket_code = $ticket['booking_reference'];
                                $total_amount = $ticket['total_amount'] == 0 ? 'Free' : '₦' . number_format($ticket['total_amount'], 2);
                            ?>
                            <div class="ticket-card">
                                <!-- Ticket Header -->
                                <div class="ticket-header">
                                    <div class="ticket-header-left">
                                        <span class="ticket-status confirmed">
                                            <i class="fas fa-check-circle"></i> Confirmed
                                        </span>
                                        <span class="ticket-code">#<?php echo htmlspecialchars($ticket_code); ?></span>
                                    </div>
                                    <div class="ticket-header-right">
                                        <span class="ticket-date"><?php echo formatDate($ticket['start_date']); ?></span>
                                    </div>
                                </div>

                                <!-- Ticket Body -->
                                <div class="ticket-body">
                                    <div class="ticket-left">
                                        <div class="ticket-qr">
                                            <div class="qr-placeholder">
                                                <!-- In real app, generate a QR code from ticket_code -->
                                                <i class="fas fa-qrcode" style="font-size: 80px; color: #333;"></i>
                                            </div>
                                            <span class="qr-label">Scan for entry</span>
                                        </div>
                                    </div>
                                    <div class="ticket-right">
                                        <h3><?php echo htmlspecialchars($ticket['event_title']); ?></h3>
                                        <div class="ticket-meta">
                                            <div class="meta-row">
                                                <i class="fas fa-calendar-day"></i>
                                                <span><?php echo formatDate($ticket['start_date']); ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo formatTime($ticket['start_date'], $ticket['end_date']); ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($ticket['venue'] . ', ' . $ticket['city']); ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-user"></i>
                                                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Attendee'); ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-chair"></i>
                                                <span>Seat: <?php echo $seat_number; ?></span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-ticket-alt"></i>
                                                <span><?php echo $ticket['ticket_quantity']; ?> tickets</span>
                                            </div>
                                            <div class="meta-row">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span><?php echo $total_amount; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ticket Footer -->
                                <div class="ticket-footer">
                                    <div class="ticket-actions">
                                        <a href="download-ticket.php?ref=<?php echo $ticket_code; ?>" class="btn-download">
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