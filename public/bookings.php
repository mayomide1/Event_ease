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

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

// ----- Fetch bookings for organizer's events -----
$sql = "
    SELECT b.*, 
           e.title as event_title, 
           e.start_date as event_date,
           u.name as customer_name,
           u.email as customer_email,
           u.phone as customer_phone
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    JOIN users u ON b.user_id = u.id
    WHERE e.organizer_id = ?
    ORDER BY b.created_at DESC
";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// ----- Calculate stats -----
$total_bookings = count($bookings);
$confirmed = count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed'));
$pending = count(array_filter($bookings, fn($b) => $b['status'] === 'pending'));

// Revenue: sum of total_amount for confirmed bookings
$revenue = 0;
foreach ($bookings as $b) {
    if ($b['status'] === 'confirmed') {
        $revenue += (float)$b['total_amount'];
    }
}
$revenue_formatted = '₦' . number_format($revenue, 2);
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
                    <p><?php echo $total_bookings; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Confirmed</h3>
                    <p><?php echo $confirmed; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <p><?php echo $pending; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Revenue</h3>
                    <p><?php echo $revenue_formatted; ?></p>
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
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $index => $booking): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['event_title']); ?></strong><br>
                                        <small><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['customer_name']); ?><br>
                                        <small><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                    </td>
                                    <td><?php echo $booking['ticket_quantity']; ?></td>
                                    <td><?php echo $booking['total_amount'] == 0 ? 'Free' : '₦' . number_format($booking['total_amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                                    <td>
                                        <span class="status <?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn-small">View</a>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <a href="confirm-booking.php?id=<?php echo $booking['id']; ?>" class="btn-small btn-confirm" onclick="return confirm('Are you sure you want to confirm this booking?');">
                                                Confirm</a>
                                        <?php endif; ?>
                                        <!-- Delete button (with confirmation) -->
                                        <?php if ($booking['status'] !== 'confirmed' || true): // Optional: restrict delete to non-confirmed ?>
                                            <a href="delete-booking.php?id=<?php echo $booking['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');">
                                                Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding: 30px;">No bookings yet for your events.</td>
                                </tr>
                            <?php endif; ?>
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