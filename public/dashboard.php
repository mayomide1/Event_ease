<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
      crossorigin="anonymous" 
      referrerpolicy="no-referrer" />
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <p>👋Welcome back, <?php echo $_SESSION['user_name'] ?? 'User'; ?>! </p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Events</h3>
                    <p>12</p>
                </div>
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p>45</p>
                </div>
                <div class="stat-card">
                    <h3>Tickets Sold</h3>
                    <p>128</p>
                </div>
                <div class="stat-card">
                    <h3>Revenue</h3>
                    <p>₦250,000</p>
                </div>
            </div>
            
            <div class="recent-events">
                <h2>Recent Events</h2>
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
                        <tr>
                            <td>Tech Conference 2026</td>
                            <td>June 20, 2026</td>
                            <td><span class="status active">Active</span></td>
                            <td><a href="#" class="btn-small">View</a></td>
                        </tr>
                        <tr>
                            <td>Music Festival</td>
                            <td>July 15, 2026</td>
                            <td><span class="status pending">Pending</span></td>
                            <td><a href="#" class="btn-small">View</a></td>
                        </tr>
                        <tr>
                            <td>Workshop: PHP</td>
                            <td>August 5, 2026</td>
                            <td><span class="status completed">Completed</span></td>
                            <td><a href="#" class="btn-small">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>