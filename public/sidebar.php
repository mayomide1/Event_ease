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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
      crossorigin="anonymous" 
      referrerpolicy="no-referrer" />
</head>
<body>
    <div class="sidebar">
        <h2><a href="index.php" class="logo"><i class="fa-solid fa-ticket"></i> EventEase</a></h2>
        <ul>
            <li><a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="my-events.php" class="<?php echo $current_page == 'my-events.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> My Events</a></li>
            <li><a href="create-event.php" class="<?php echo $current_page == 'create-event.php' ? 'active' : ''; ?>"><i class="fas fa-plus-circle"></i> Create New Event</a></li>
            <li><a href="bookings.php" class="<?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div> 
</body>
</html>