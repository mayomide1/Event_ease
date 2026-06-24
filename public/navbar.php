<?php 
    $current_page = basename($_SERVER['PHP_SELF']);
?>
    <div class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php" class="logo"><i class="fas fa-ticket-alt"></i> EventEase</a>
            </div>
            
            <ul class="nav-menu">
                <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="events.php">Browse Events</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] == 'organizer'): ?>
                        <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="my-bookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="signup.php" class="btn-signup"><i class="fas fa-user-plus"></i> Sign Up</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>