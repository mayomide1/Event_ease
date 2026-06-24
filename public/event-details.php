<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    $_SESSION['error'] = "Invalid event ID.";
    header('Location: browse.php');
    exit();
}

$db = Database::getConnection();

// Fetch event details with category and organizer info
$stmt = $db->prepare("
    SELECT e.*, 
           c.name as category_name, 
           u.name as organizer_name, 
           u.email as organizer_email,
           u.phone as organizer_phone
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    LEFT JOIN users u ON e.organizer_id = u.id
    WHERE e.id = ? AND e.status = 'published'
");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

// If event not found or not published, redirect
if (!$event) {
    $_SESSION['error'] = "Event not found or not available.";
    header('Location: browse.php');
    exit();
}

// Prepare display data
$event_data = [
    'id' => $event['id'],
    'title' => $event['title'],
    'category' => $event['category_name'] ?? 'Uncategorized',
    'date' => date('F j, Y', strtotime($event['start_date'])),
    'time' => date('g:i A', strtotime($event['start_date'])) . ' - ' . date('g:i A', strtotime($event['end_date'])),
    'venue' => $event['venue'],
    'address' => $event['address'] ?? '',
    'city' => $event['city'] ?? '',
    'state' => $event['state'] ?? '',
    'country' => $event['country'] ?? 'Nigeria',
    'price' => $event['is_free'] ? 'Free' : '₦' . number_format($event['price'], 2),
    'price_numeric' => $event['price'],
    'is_free' => $event['is_free'],
    'image' => $event['image'] ?? 'placeholder.jpg',
    'description' => $event['description'],
    'capacity' => $event['capacity'],
    'tickets_sold' => $event['tickets_sold'],
    'available_tickets' => $event['capacity'] - $event['tickets_sold'],
    'organizer' => $event['organizer_name'],
    'organizer_email' => $event['organizer_email'] ?? 'N/A',
    'organizer_phone' => $event['organizer_phone'] ?? 'N/A',
    'status' => $event['status']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event_data['title']); ?> - EventEase</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/event-details.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Event Details -->
    <section class="event-details">
        <div class="container">
            <div class="details-grid">
                <!-- Left Column - Event Info -->
                <div class="event-info">
                    <nav class="breadcrumb">
                        <a href="index.php">Home</a>
                        <span class="separator">›</span>
                        <a href="browse.php">Events</a>
                        <span class="separator">›</span>
                        <span class="current"><?php echo htmlspecialchars($event_data['title']); ?></span>
                    </nav>

                    <h1><?php echo htmlspecialchars($event_data['title']); ?></h1>
                    <div class="event-badges">
                        <span class="category-badge"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event_data['category']); ?></span>
                        <?php if ($event_data['is_free']): ?>
                            <span class="price-badge free"><i class="fas fa-gift"></i> Free</span>
                        <?php else: ?>
                            <span class="price-badge"> <?php echo $event_data['price']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="event-description-full">
                        <h3><i class="fas fa-info-circle"></i> About This Event</h3>
                        <p><?php echo nl2br(htmlspecialchars($event_data['description'])); ?></p>
                    </div>

                    <div class="event-details-grid">
                        <div class="detail-item">
                            <i class="fas fa-calendar-day"></i>
                            <div>
                                <span class="label">Date</span>
                                <span class="value"><?php echo $event_data['date']; ?></span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="label">Time</span>
                                <span class="value"><?php echo $event_data['time']; ?></span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="label">Venue</span>
                                <span class="value"><?php echo htmlspecialchars($event_data['venue']); ?></span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-location-dot"></i>
                            <div>
                                <span class="label">Location</span>
                                <span class="value"><?php echo htmlspecialchars($event_data['city'] . ', ' . $event_data['state']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="organizer-info">
                        <h3><i class="fas fa-user-tie"></i> Organized By</h3>
                        <div class="organizer-details">
                            <div class="organizer-avatar">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h4><?php echo htmlspecialchars($event_data['organizer']); ?></h4>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($event_data['organizer_email']); ?></p>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($event_data['organizer_phone']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Booking Card -->
                <div class="booking-card">
                    <div class="card-header">
                        <h3><i class="fas fa-ticket-alt"></i> Book Tickets</h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="price-display">
                            <span class="price-label">Price per ticket</span>
                            <span class="price-amount"><?php echo $event_data['price']; ?></span>
                        </div>

                        <div class="tickets-available">
                            <i class="fas fa-ticket-alt"></i>
                            <span><?php echo $event_data['available_tickets']; ?> tickets available</span>
                        </div>

                        <form action="book-ticket.php" method="POST">
                            <input type="hidden" name="event_id" value="<?php echo $event_data['id']; ?>">
                            
                            <div class="form-group">
                                <label for="quantity">Select Tickets</label>
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn" id="decreaseQty">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $event_data['available_tickets']; ?>">
                                    <button type="button" class="qty-btn" id="increaseQty">+</button>
                                </div>
                            </div>

                            <div class="total-display">
                                <span>Total</span>
                                <span class="total-amount"><?php echo $event_data['price']; ?></span>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button type="submit" class="btn-book-now">
                                    <i class="fas fa-shopping-cart"></i> Book Now
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn-book-now btn-login-required">
                                    <i class="fas fa-sign-in-alt"></i> Login to Book
                                </a>
                                <p class="login-note">Please <a href="login.php">login</a> or <a href="signup.php">sign up</a> to book tickets</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        // Mobile Hamburger Menu
        document.querySelector('.hamburger').addEventListener('click', function() {
            this.classList.toggle('active');
            document.querySelector('.nav-menu').classList.toggle('active');
        });

        // Quantity Selector
        document.getElementById('decreaseQty').addEventListener('click', function() {
            let input = document.getElementById('quantity');
            let current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
                updateTotal();
            }
        });

        document.getElementById('increaseQty').addEventListener('click', function() {
            let input = document.getElementById('quantity');
            let current = parseInt(input.value);
            let max = parseInt(input.max);
            if (current < max) {
                input.value = current + 1;
                updateTotal();
            }
        });

        document.getElementById('quantity').addEventListener('change', function() {
            updateTotal();
        });

        function updateTotal() {
            let quantity = parseInt(document.getElementById('quantity').value);
            let price = <?php echo $event_data['price_numeric']; ?>;
            let total = quantity * price;
            let formatted = '₦' + total.toLocaleString();
            document.querySelector('.total-amount').textContent = formatted;
        }
    </script>
</body>
</html>