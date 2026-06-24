<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Combine first and last name
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $name = $firstname . ' ' . $lastname;
    
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');  // <-- FIX: Get phone number
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($phone) || empty($password)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: signup.php');
        exit();
    }
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: signup.php');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address.';
        header('Location: signup.php');
        exit();
    }

    // Check if email already exists
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email already registered.';
        header('Location: signup.php');
        exit();
    }

    // Hash password and insert (including phone)
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $phone, $hashed, $role])) {
        $_SESSION['success'] = 'Account created! Please login.';
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = 'Signup failed. Please try again.';
        header('Location: signup.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - EventEase</title>
    <link rel="stylesheet" href="assets/css/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">
        <div class="signup-box">
            <div class="logo">
                <h2>Create Account</h2>
                <h3>Join us today!</h3>
                
                <!-- Show error/success messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <form action="signup.php" method="POST">
                <div class="name-row">
                    <div class="input-group half">
                        <label for="firstname">First Name <span class="required">*</span></label>
                        <input type="text" id="firstname" name="firstname" placeholder="John" required>
                    </div>
                    <div class="input-group half">
                        <label for="lastname">Last Name <span class="required">*</span></label>
                        <input type="text" id="lastname" name="lastname" placeholder="Doe" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <!-- FIX: name="phone" matches the database column -->
                <div class="input-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                </div>
                
                <div class="input-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                
                <!-- Fixed Role Selection -->
                <div class="input-group">
                    <label for="role">I want to...</label>
                    <select id="role" name="role" class="role-select">
                        <option value="user">Book events (User)</option>
                        <option value="organizer">Create events (Organizer)</option>
                    </select>
                </div>
                
                <div class="terms">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn">Create Account</button>
            </form>
            <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>