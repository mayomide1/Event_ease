<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
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
    
    if (empty($category)) {
        $errors['category'] = "Please select a category";
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
    
    // If no errors, process the event
    if (empty($errors)) {
        // Here you would save to database
        // For now, we'll just show success message
        $success = true;
        $_SESSION['success'] = "Event created successfully!";
        
        // Redirect or show success
        // header('Location: events.php');
        // exit();
    }
}

// Get categories (simulated - replace with database later)
$categories = [
    'Conference', 'Workshop', 'Seminar', 'Concert', 
    'Festival', 'Exhibition', 'Networking', 'Party', 
    'Sports', 'Charity', 'Other'
];
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
</head>
<body>
    <!-- Mobile Menu Toggle Button -->
    <!-- <button class="sidebar-toggle" id="sidebarToggle">☰</button> -->
    
    <!-- Sidebar Overlay for Mobile -->
    <!-- <div class="sidebar-overlay" id="sidebarOverlay"></div>
     -->
    <!-- Include Sidebar Component -->
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Create New Event</h1>
                <p>Fill in the details to create a new event</p>
            </div>
            <a href="my-events.php" class="btn-back">← Back to My Events</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ Event created successfully! 
                <a href="events.php">View all events →</a>
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
                    <h2>📋 Basic Information</h2>
                    
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
                            <label for="category">Category <span class="required">*</span></label>
                            <select id="category" name="category" 
                                    class="<?php echo isset($errors['category']) ? 'error' : ''; ?>"
                                    required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" 
                                        <?php echo (($_POST['category'] ?? '') === $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
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
                    <h2>📍 Location Details</h2>
                    
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
                    <h2>📅 Date & Time</h2>
                    
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
                    <h2>🎟️ Tickets & Pricing</h2>
                    
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
                    <h2>🖼️ Event Image</h2>
                    
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
                    <button type="submit" class="btn btn-primary">✅ Create Event</button>
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            }
            
            toggleBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
            
            // Auto-set end date when start date changes
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            startDateInput.addEventListener('change', function() {
                if (this.value && !endDateInput.value) {
                    const start = new Date(this.value);
                    start.setHours(start.getHours() + 2); // Add 2 hours
                    endDateInput.value = start.toISOString().slice(0, 16);
                }
            });
            
            // Free event checkbox - auto set price to 0
            const freeCheckbox = document.getElementById('is_free');
            const priceInput = document.getElementById('price');
            
            freeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    priceInput.value = 0;
                    priceInput.disabled = true;
                } else {
                    priceInput.disabled = false;
                }
            });
            
            // File upload visual feedback
            const fileInput = document.getElementById('event_image');
            const fileLabel = document.querySelector('.file-upload-label');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const fileName = this.files[0].name;
                    fileLabel.innerHTML = `
                        <span class="upload-icon">✅</span>
                        <p>${fileName}</p>
                        <small>Click to change image</small>
                    `;
                }
            });
        });
    </script>
</body>
</html>