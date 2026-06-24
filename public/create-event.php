<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is organizer/admin
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

// Fetch categories from database
$stmt = $db->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $title = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $capacity = (int)($_POST['capacity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    
    // ----- Validation -----
    if (empty($title)) {
        $errors['title'] = "Event title is required";
    } elseif (strlen($title) < 5) {
        $errors['title'] = "Event title must be at least 5 characters";
    }
    
    if ($category_id <= 0) {
        $errors['category'] = "Please select a valid category";
    }
    
    if (empty($description)) {
        $errors['description'] = "Event description is required";
    } elseif (strlen($description) < 20) {
        $errors['description'] = "Description must be at least 20 characters";
    }
    
    if (empty($venue)) {
        $errors['venue'] = "Venue is required";
    }
    
    if (empty($city)) {
        $errors['city'] = "City is required";
    }
    
    if (empty($start_date)) {
        $errors['start_date'] = "Start date is required";
    }
    
    if (empty($end_date)) {
        $errors['end_date'] = "End date is required";
    } elseif ($end_date < $start_date) {
        $errors['end_date'] = "End date must be after start date";
    }
    
    if ($capacity < 1) {
        $errors['capacity'] = "Capacity must be at least 1";
    }
    
    if ($price < 0) {
        $errors['price'] = "Price cannot be negative";
    }
    
    // Handle image upload
    $image_name = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/assets/images/events/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_tmp = $_FILES['event_image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['event_image']['name']);
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $file_path)) {
            $image_name = $file_name;
        } else {
            $errors['image'] = "Failed to upload image.";
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO events 
                (organizer_id, category_id, title, description, venue, address, city, state, 
                 start_date, end_date, capacity, price, is_free, status, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $category_id,
                $title,
                $description,
                $venue,
                $address,
                $city,
                $state,
                $start_date,
                $end_date,
                $capacity,
                $price,
                $is_free,
                $status,
                $image_name
            ]);
            $_SESSION['success'] = "Event created successfully!";
            header('Location: my-events.php');
            exit();
        } catch (PDOException $e) {
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/create-event.css">
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
                    <h1>Create New Event</h1>
                    <p>Fill in the details to create a new event</p>
                </div>
                <a href="my-events.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to My Events</a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Event created successfully! 
                    <a href="my-events.php">View my events →</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $field => $message): ?>
                            <li>• <?php echo htmlspecialchars($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="create-event-form">
                <form action="create-event.php" method="POST" enctype="multipart/form-data">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h2><i class="far fa-clipboard"></i> Basic Information</h2>
                        
                        <div class="form-group">
                            <label for="title">Event Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                   placeholder="Enter event title (e.g., Tech Conference 2026)"
                                   class="<?php echo isset($errors['title']) ? 'error' : ''; ?>"
                                   required>
                            <?php if (isset($errors['title'])): ?>
                                <span class="error-message"><?php echo $errors['title']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category <span class="required">*</span></label>
                                <select id="category_id" name="category_id" 
                                        class="<?php echo isset($errors['category']) ? 'error' : ''; ?>"
                                        required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category'])): ?>
                                    <span class="error-message"><?php echo $errors['category']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Event Status</label>
                                <select id="status" name="status">
                                    <option value="draft" <?php echo (($_POST['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo (($_POST['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" 
                                      rows="5"
                                      class="<?php echo isset($errors['description']) ? 'error' : ''; ?>"
                                      placeholder="Describe your event in detail... (minimum 20 characters)"
                                      required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <small class="helper-text">Min 20 characters. Be descriptive to attract attendees.</small>
                            <?php if (isset($errors['description'])): ?>
                                <span class="error-message"><?php echo $errors['description']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Location Section -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-location-dot"></i> Location Details</h2>
                        
                        <div class="form-group">
                            <label for="venue">Venue <span class="required">*</span></label>
                            <input type="text" id="venue" name="venue" 
                                   value="<?php echo htmlspecialchars($_POST['venue'] ?? ''); ?>"
                                   placeholder="e.g., Main Hall, Conference Center"
                                   class="<?php echo isset($errors['venue']) ? 'error' : ''; ?>"
                                   required>
                            <?php if (isset($errors['venue'])): ?>
                                <span class="error-message"><?php echo $errors['venue']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Full Address</label>
                            <input type="text" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                                   placeholder="e.g., 123 University Road">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City <span class="required">*</span></label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>"
                                       placeholder="e.g., Lagos"
                                       class="<?php echo isset($errors['city']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['city'])): ?>
                                    <span class="error-message"><?php echo $errors['city']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>"
                                       placeholder="e.g., Lagos State">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date & Time Section -->
                    <div class="form-section">
                        <h2><i class="far fa-calendar-alt"></i> Date & Time</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date & Time <span class="required">*</span></label>
                                <input type="datetime-local" id="start_date" name="start_date" 
                                       value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>"
                                       class="<?php echo isset($errors['start_date']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['start_date'])): ?>
                                    <span class="error-message"><?php echo $errors['start_date']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_date">End Date & Time <span class="required">*</span></label>
                                <input type="datetime-local" id="end_date" name="end_date" 
                                       value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>"
                                       class="<?php echo isset($errors['end_date']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['end_date'])): ?>
                                    <span class="error-message"><?php echo $errors['end_date']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tickets & Pricing Section -->
                    <div class="form-section">
                        <h2><i class="fas fa-ticket-alt"></i> Tickets & Pricing</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="capacity">Event Capacity <span class="required">*</span></label>
                                <input type="number" id="capacity" name="capacity" 
                                       value="<?php echo htmlspecialchars($_POST['capacity'] ?? 100); ?>"
                                       min="1" max="10000"
                                       class="<?php echo isset($errors['capacity']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['capacity'])): ?>
                                    <span class="error-message"><?php echo $errors['capacity']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Ticket Price (₦) <span class="required">*</span></label>
                                <input type="number" id="price" name="price" 
                                       value="<?php echo htmlspecialchars($_POST['price'] ?? 0); ?>"
                                       min="0" step="100"
                                       class="<?php echo isset($errors['price']) ? 'error' : ''; ?>"
                                       required>
                                <?php if (isset($errors['price'])): ?>
                                    <span class="error-message"><?php echo $errors['price']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_free" name="is_free" 
                                   <?php echo isset($_POST['is_free']) ? 'checked' : ''; ?>>
                            <label for="is_free">This is a free event</label>
                        </div>
                    </div>
                    
                    <!-- Event Image Section -->
                    <div class="form-section">
                        <h2><i class="far fa-image"></i> Event Image</h2>
                        
                        <div class="form-group">
                            <label for="event_image">Upload Event Banner</label>
                            <div class="file-upload">
                                <input type="file" id="event_image" name="event_image" 
                                       accept="image/*">
                                <div class="file-upload-label">
                                    <span class="upload-icon">📤</span>
                                    <p>Click or drag to upload image</p>
                                    <small>Recommended: 1200x600px, Max 5MB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Create Event</button>
                        <a href="my-events.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
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