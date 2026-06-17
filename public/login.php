<?php
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Demo login (replace with database later)
    if ($email === 'admin@eventease.com' && $password === 'password123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Admin User';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'admin';
        
        $_SESSION['success'] = 'Login successful!';
        header('Location: dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = 'Invalid email or password!';
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <h2>Welcome Back</h2>
                <h3>Login to your account</h3>
            </div>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="remember">
                    <input type="checkbox" id="remember">
                    <label for="remember">Remember me</label>
                    <a href="#" class="forgot">Forgot password?</a>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <p class="signup-link">Don't have an account? <a href="signup.php">Create an account</a></p>
        </div>
    </div>
</body>
</html>