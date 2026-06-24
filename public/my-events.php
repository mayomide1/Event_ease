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

// Fetch events created by this organizer
$stmt = $db->prepare("
    SELECT e.*, c.name as category_name
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    WHERE e.organizer_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$user_id]);
$events = $stmt->fetchAll();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
      crossorigin="anonymous" 
      referrerpolicy="no-referrer" />
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Hamburger button -->
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="page-header">
                <div>
                    <h1>My Events</h1>
                    <p>Manage all your events in one place</p>
                </div>
                <a href="create-event.php" class="btn-create"><i class="fa-solid fa-plus"></i> Create New Event</a>
            </div>

            <div class="events-grid">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="assets/images/events/<?php echo htmlspecialchars($event['image'] ?? 'placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($event['title']); ?>"
                                     onerror="this.src='assets/images/event-placeholder.jpg'">
                                <span class="event-status <?php echo $event['status']; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </div>
                            <div class="event-body">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-description">
                                    <?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 80)) . '...'; ?>
                                </p>
                                <div class="event-meta">
                                    <span class="meta-item"><i class="far fa-calendar-alt"></i> 
                                        <?php echo date('M d, Y', strtotime($event['start_date'])); ?>
                                    </span>
                                    <span class="meta-item"><i class="fa-solid fa-location-dot"></i> 
                                        <?php echo htmlspecialchars($event['city'] ?? 'N/A'); ?>
                                    </span>
                                </div>
                                <div class="event-stats">
                                    <span><i class="fas fa-ticket-alt"></i> 
                                        <?php echo $event['tickets_sold']; ?>/<?php echo $event['capacity']; ?> sold
                                    </span>
                                    <span><i class="fa-solid fa-sack-dollar"></i> 
                                        <?php echo $event['is_free'] ? 'Free' : '₦' . number_format($event['price'], 2); ?>
                                    </span>
                                </div>
                                <div class="event-actions">
                                    <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-view">View Details</a>
                                    <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn-edit"><i class="fa-solid fa-file-pen"></i> Edit</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-events">
                        <i class="fas fa-calendar-plus" style="font-size: 48px; color: #ccc;"></i>
                        <h3>No Events Yet</h3>
                        <p>You haven't created any events. Start by creating your first event!</p>
                        <a href="create-event.php" class="btn-create"><i class="fa-solid fa-plus"></i> Create Event</a>
                    </div>
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