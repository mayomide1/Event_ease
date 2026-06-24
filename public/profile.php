<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$password_success = $_SESSION['success'] ?? null;
$password_errors = $_SESSION['password_errors'] ?? [];
unset($_SESSION['success'], $_SESSION['password_errors']);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

// Fetch user data from database
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    $_SESSION['error'] = "User not found. Please login again.";
    header('Location: login.php');
    exit();
}

// Update session with fresh data (optional but good practice)
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_phone'] = $user['phone'] ?? '';
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_created_at'] = $user['created_at'];

// Prepare display data
$user_data = [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone' => $user['phone'] ?? 'Not provided',
    'role' => $user['role'],
    'created_at' => date('F j, Y', strtotime($user['created_at'])),
    'bio' => ($user['role'] === 'organizer' || $user['role'] === 'admin') 
        ? 'Event organizer with 5 years of experience in tech events.' 
        : 'Event enthusiast and regular attendee.',
    'organization' => ($user['role'] === 'organizer' || $user['role'] === 'admin') 
        ? 'EventEase Ltd.' 
        : 'N/A',
    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=667eea&color=fff&size=150'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/profile.css">
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
                <h1>Profile</h1>
                <p><i class="fa-regular fa-user"></i> Manage your account information</p>
            </div>

            <!-- Profile Card -->
            <div class="profile-container">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="<?php echo $user_data['avatar']; ?>" alt="Avatar">
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user_data['name']); ?></h2>
                            <p class="profile-role"><i class="fas fa-badge-check"></i> <?php echo ucfirst($user_data['role']); ?></p>
                            <?php if ($user_data['role'] === 'organizer' || $user_data['role'] === 'admin'): ?>
                                <p class="profile-org"><i class="fas fa-building"></i> <?php echo $user_data['organization']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="profile-body">
                        <div class="profile-details">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-envelope"></i> Email</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-phone"></i> Phone</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user_data['phone']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-calendar-alt"></i> Joined</span>
                                <span class="detail-value"><?php echo $user_data['created_at']; ?></span>
                            </div>
                        </div>

                        <?php if ($user_data['role'] === 'organizer' || $user_data['role'] === 'admin'): ?>
                            <div class="profile-stats">
                                <div class="stat-box">
                                    <span class="stat-number">12</span>
                                    <span class="stat-label">Events Created</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number">45</span>
                                    <span class="stat-label">Total Bookings</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number">128</span>
                                    <span class="stat-label">Tickets Sold</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number">₦250K</span>
                                    <span class="stat-label">Revenue</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="profile-stats user-stats">
                                <div class="stat-box">
                                    <span class="stat-number">6</span>
                                    <span class="stat-label">Events Attended</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number">4</span>
                                    <span class="stat-label">Bookings</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number">12</span>
                                    <span class="stat-label">Tickets Purchased</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-number">₦85K</span>
                                    <span class="stat-label">Total Spent</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="profile-card password-card">
                    <h3><i class="fas fa-lock"></i> Change Password</h3>

                        <?php if ($password_success): ?>
                            <div class="alert alert-success"><?php echo $password_success; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($password_errors)): ?>
                            <div class="alert alert-error">
                                <ul>
                                    <?php foreach ($password_errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <form action="change-password.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Update Password</button>
                    </form>
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