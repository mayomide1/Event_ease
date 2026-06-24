<?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_logged_in = isset($_SESSION['user_id']);
    $user_name = $_SESSION['user_name'] ?? 'Guest';
    $user_email = $_SESSION['user_email'] ?? '';
    $user_role = $_SESSION['user_role'] ?? '';
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2><a href="index.php" class="logo"><i class="fas fa-ticket-alt"></i> EventEase</a></h2>
        <?php if ($is_logged_in): ?>
            <div class="user-info">
                <div class="user-avatar">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=667eea&color=fff&size=50" alt="Avatar">
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                    <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
                    <span class="user-role"><i class="fas fa-badge-check"></i> <?php echo ucfirst($user_role); ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="user-info guest">
                <span class="guest-text">Welcome, Guest</span>
                <a href="login.php" class="guest-login">Login</a>
            </div>
        <?php endif; ?>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'organizer'): ?>
                <!-- ORGANIZER MENU -->
                <li>
                    <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="my-events.php" class="<?php echo $current_page == 'my-events.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> My Events
                    </a>
                </li>
                <li>
                    <a href="create-event.php" class="<?php echo $current_page == 'create-event.php' ? 'active' : ''; ?>">
                        <i class="fas fa-plus-circle"></i> Create Event
                    </a>
                </li>
                <li>
                    <a href="bookings.php" class="<?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-ticket-alt"></i> Bookings
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="logout.php" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>

            <?php elseif (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'user'): ?>
                <!-- USER MENU -->
                <li>
                    <a href="events.php" class="<?php echo $current_page == 'events.php' ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i> Browse Events
                    </a>
                </li>
                <li>
                    <a href="my-bookings.php" class="<?php echo $current_page == 'my-bookings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-ticket-alt"></i> My Bookings
                    </a>
                </li>
                <li>
                    <a href="my-tickets.php" class="<?php echo $current_page == 'my-tickets.php' ? 'active' : ''; ?>">
                        <i class="fas fa-qrcode"></i> My Tickets
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="logout.php" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>

            <?php else: ?>
                <!-- GUEST MENU (Not Logged In) -->
                <li>
                    <a href="browse.php" class="<?php echo $current_page == 'browse.php' ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i> Browse Events
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="login.php" class="<?php echo $current_page == 'login.php' ? 'active' : ''; ?>">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li>
                    <a href="signup.php" class="<?php echo $current_page == 'signup.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>