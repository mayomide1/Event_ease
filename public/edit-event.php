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

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id <= 0) {
    $_SESSION['error'] = "Invalid event ID.";
    header('Location: my-events.php');
    exit();
}

// Fetch event data and verify ownership
$stmt = $db->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch();

if (!$event) {
    $_SESSION['error'] = "Event not found or you don't have permission to edit it.";
    header('Location: my-events.php');
    exit();
}

// Fetch categories for dropdown
$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
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

    // Validation
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

    // Handle image upload if a new file is provided
    $image_name = $event['image']; // Keep old image by default
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
            // Delete old image if exists
            if ($event['image'] && file_exists($upload_dir . $event['image'])) {
                unlink($upload_dir . $event['image']);
            }
        } else {
            $errors['image'] = "Failed to upload image.";
        }
    }

    // If no errors, update the database
    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE events SET
                category_id = ?,
                title = ?,
                description = ?,
                venue = ?,
                address = ?,
                city = ?,
                state = ?,
                start_date = ?,
                end_date = ?,
                capacity = ?,
                price = ?,
                is_free = ?,
                status = ?,
                image = ?
            WHERE id = ? AND organizer_id = ?
        ");
        $updated = $stmt->execute([
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
            $image_name,
            $event_id,
            $user_id
        ]);
        if ($updated) {
            $_SESSION['success'] = "Event updated successfully!";
            header('Location: my-events.php');
            exit();
        } else {
            $errors['db'] = "Failed to update event.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - EventEase</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/create-event.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="main-content" id="mainContent">
            <div class="page-header">
                <div>
                    <h1>Edit Event</h1>
                    <p>Update your event details</p>
                </div>
                <a href="my-events.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to My Events</a>
            </div>

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
                <form action="edit-event.php?id=<?php echo $event_id; ?>" method="POST" enctype="multipart/form-data">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h2><i class="far fa-clipboard"></i> Basic Information</h2>
                        <div class="form-group">
                            <label for="title">Event Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($event['title']); ?>"
                                   class="<?php echo isset($errors['title']) ? 'error' : ''; ?>" required>
                            <?php if (isset($errors['title'])): ?>
                                <span class="error-message"><?php echo $errors['title']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category <span class="required">*</span></label>
                                <select id="category_id" name="category_id" 
                                        class="<?php echo isset($errors['category']) ? 'error' : ''; ?>" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $event['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
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
                                    <option value="draft" <?php echo $event['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $event['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" rows="5"
                                      class="<?php echo isset($errors['description']) ? 'error' : ''; ?>" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                            <small class="helper-text">Min 20 characters. Be descriptive to attract attendees.</small>
                            <?php if (isset($errors['description'])): ?>
                                <span class="error-message"><?php echo $errors['description']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-location-dot"></i> Location Details</h2>
                        <div class="form-group">
                            <label for="venue">Venue <span class="required">*</span></label>
                            <input type="text" id="venue" name="venue" 
                                   value="<?php echo htmlspecialchars($event['venue']); ?>"
                                   class="<?php echo isset($errors['venue']) ? 'error' : ''; ?>" required>
                            <?php if (isset($errors['venue'])): ?>
                                <span class="error-message"><?php echo $errors['venue']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="address">Full Address</label>
                            <input type="text" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($event['address']); ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City <span class="required">*</span></label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($event['city']); ?>"
                                       class="<?php echo isset($errors['city']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['city'])): ?>
                                    <span class="error-message"><?php echo $errors['city']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($event['state']); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="form-section">
                        <h2><i class="far fa-calendar-alt"></i> Date & Time</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date & Time <span class="required">*</span></label>
                                <input type="datetime-local" id="start_date" name="start_date" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_date'])); ?>"
                                       class="<?php echo isset($errors['start_date']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['start_date'])): ?>
                                    <span class="error-message"><?php echo $errors['start_date']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date & Time <span class="required">*</span></label>
                                <input type="datetime-local" id="end_date" name="end_date" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_date'])); ?>"
                                       class="<?php echo isset($errors['end_date']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['end_date'])): ?>
                                    <span class="error-message"><?php echo $errors['end_date']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="form-section">
                        <h2><i class="fas fa-ticket-alt"></i> Tickets & Pricing</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="capacity">Event Capacity <span class="required">*</span></label>
                                <input type="number" id="capacity" name="capacity" 
                                       value="<?php echo $event['capacity']; ?>" min="1" max="10000"
                                       class="<?php echo isset($errors['capacity']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['capacity'])): ?>
                                    <span class="error-message"><?php echo $errors['capacity']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="price">Ticket Price (₦) <span class="required">*</span></label>
                                <input type="number" id="price" name="price" 
                                       value="<?php echo $event['price']; ?>" min="0" step="100"
                                       class="<?php echo isset($errors['price']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['price'])): ?>
                                    <span class="error-message"><?php echo $errors['price']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_free" name="is_free" <?php echo $event['is_free'] ? 'checked' : ''; ?>>
                            <label for="is_free">This is a free event</label>
                        </div>
                    </div>

                    <!-- Event Image -->
                    <div class="form-section">
                        <h2><i class="far fa-image"></i> Event Image</h2>
                        <?php if ($event['image']): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="assets/images/events/<?php echo $event['image']; ?>" alt="Current Image" style="max-width: 200px; border-radius: 8px;">
                                <p><small>Current image</small></p>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="event_image">Upload New Banner (leave blank to keep current)</label>
                            <div class="file-upload">
                                <input type="file" id="event_image" name="event_image" accept="image/*">
                                <div class="file-upload-label">
                                    <span class="upload-icon">📤</span>
                                    <p>Click or drag to upload new image</p>
                                    <small>Recommended: 1200x600px, Max 5MB</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Event</button>
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