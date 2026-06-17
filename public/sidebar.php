<?php 
    // Get the current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SideBar</title>
    <link rel="stylesheet" href="assets/css/sidebar.css">
</head>
<body>
    <div class="sidebar">
        <h2><a href="index.php" class="logo">🎫 EventEase</a></h2>
        <ul>
            <li><a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">🏠 Dashboard</a></li>
            <li><a href="my-events.php" class="<?php echo $current_page == 'my-events.php' ? 'active' : ''; ?>">📅 My Events</a></li>
            <li><a href="create-event.php" class="<?php echo $current_page == 'create-event.php' ? 'active' : ''; ?>">➕ Create New Event</a></li>
            <li><a href="bookings.php" class="<?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>">🎟️ Bookings</a></li>
            <li><a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">👤 Profile</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div> 
</body>
</html>