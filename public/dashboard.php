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

// ----- Fetch statistics -----
// Total Events created by this organizer
$stmt = $db->prepare("SELECT COUNT(*) as total FROM events WHERE organizer_id = ?");
$stmt->execute([$user_id]);
$total_events = $stmt->fetch()['total'];

// Total Bookings for organizer's events
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM bookings b 
    JOIN events e ON b.event_id = e.id 
    WHERE e.organizer_id = ?
");
$stmt->execute([$user_id]);
$total_bookings = $stmt->fetch()['total'];

// Tickets Sold (sum of ticket_quantity for confirmed bookings)
$stmt = $db->prepare("
    SELECT COALESCE(SUM(b.ticket_quantity), 0) as sold 
    FROM bookings b 
    JOIN events e ON b.event_id = e.id 
    WHERE e.organizer_id = ? AND b.status = 'confirmed'
");
$stmt->execute([$user_id]);
$tickets_sold = $stmt->fetch()['sold'];

// Revenue (sum of total_amount for confirmed bookings)
$stmt = $db->prepare("
    SELECT COALESCE(SUM(b.total_amount), 0) as revenue 
    FROM bookings b 
    JOIN events e ON b.event_id = e.id 
    WHERE e.organizer_id = ? AND b.status = 'confirmed'
");
$stmt->execute([$user_id]);
$revenue = $stmt->fetch()['revenue'];

// ----- Recent Events (latest 5) -----
$stmt = $db->prepare("
    SELECT id, title, start_date, status 
    FROM events 
    WHERE organizer_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
                <h1>Dashboard</h1>
                <p><i class="fa-regular fa-hand-spock"></i> Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Events</h3>
                    <p><?php echo $total_events; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Tickets Sold</h3>
                    <p><?php echo $tickets_sold; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Revenue</h3>
                    <p>₦<?php echo number_format($revenue, 2); ?></p>
                </div>
            </div>
            
            <div class="recent-events">
                <h2>Recent Events</h2>
                <?php if (count($recent_events) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($event['start_date'])); ?></td>
                                    <td><span class="status <?php echo $event['status']; ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span></td>
                                    <td><a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-small">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No events created yet. <a href="create-event.php">Create your first event</a>.</p>
                <?php endif; ?>
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