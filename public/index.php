<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$current_page = basename($_SERVER['PHP_SELF']);
$db = Database::getConnection();

// ----- Fetch featured events (latest published events) -----
$stmt = $db->query("
    SELECT e.*, c.name as category_name, u.name as organizer_name
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    LEFT JOIN users u ON e.organizer_id = u.id
    WHERE e.status = 'published'
    ORDER BY e.created_at DESC
    LIMIT 6
");
$featured_events = $stmt->fetchAll();

// ----- Fetch upcoming events (soonest first) -----
$stmt = $db->query("
    SELECT e.*, c.name as category_name
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    WHERE e.status = 'published' AND e.start_date > NOW()
    ORDER BY e.start_date ASC
    LIMIT 3
");
$upcoming_events = $stmt->fetchAll();

// ----- Fetch category counts for stats -----
$stmt = $db->query("
    SELECT c.name, c.slug, COUNT(e.id) as event_count
    FROM categories c
    LEFT JOIN events e ON e.category_id = c.id AND e.status = 'published'
    GROUP BY c.id
");
$categories_stats = $stmt->fetchAll();

// ----- Fetch overall stats -----
$total_events = $db->query("SELECT COUNT(*) as total FROM events WHERE status = 'published'")->fetch()['total'];
$total_attendees = $db->query("SELECT SUM(tickets_sold) as total FROM events WHERE status = 'published'")->fetch()['total'] ?? 0;
$total_venues = $db->query("SELECT COUNT(DISTINCT venue) as total FROM events WHERE status = 'published'")->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventEase - Find & Book Events</title>
    <link rel="stylesheet" href="assets/css/homepage.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Amazing <span>Events</span></h1>
            <p>Find and book tickets for the best events happening near you. From concerts to conferences, we've got you covered.</p>
            <div class="hero-buttons">
                <a href="events.php" class="btn btn-primary">Browse Events</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="signup.php" class="btn btn-secondary">Get Started →</a>
                <?php endif; ?>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <span class="number"><?php echo $total_events; ?>+</span>
                    <span class="label"><i class="fas fa-calendar-check"></i> Events Hosted</span>
                </div>
                <div class="stat">
                    <span class="number"><?php echo number_format($total_attendees); ?>+</span>
                    <span class="label"><i class="fas fa-users"></i> Happy Attendees</span>
                </div>
                <div class="stat">
                    <span class="number"><?php echo $total_venues; ?>+</span>
                    <span class="label"><i class="fas fa-building"></i> Partner Venues</span>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero-events.jpg" alt="Events" onerror="this.style.display='none'">
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <div class="section-header">
                <h2>Browse by Category</h2>
                <p>Find events that match your interests</p>
            </div>
            <div class="category-grid">
                <?php 
                // You can map icons to categories via a helper or hardcoded, but we can also use the first letter or an icon map:
                $icon_map = [
                    'Conference' => 'fa-briefcase',
                    'Concert' => 'fa-music',
                    'Workshop' => 'fa-tools',
                    'Charity' => 'fa-heart',
                    'Sports' => 'fa-futbol',
                    'Exhibition' => 'fa-palette'
                ];
                $color_map = [
                    'Conference' => '#4A90D9',
                    'Concert' => '#E74C3C',
                    'Workshop' => '#F39C12',
                    'Charity' => '#E74C3C',
                    'Sports' => '#27AE60',
                    'Exhibition' => '#8E44AD'
                ];
                foreach ($categories_stats as $cat): 
                    $icon = $icon_map[$cat['name']] ?? 'fa-tag';
                    $color = $color_map[$cat['name']] ?? '#667eea';
                ?>
                    <a href="events.php?category=<?php echo $cat['slug']; ?>" class="category-card">
                        <span class="icon"><i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i></span>
                        <h3><?php echo $cat['name']; ?></h3>
                        <p><?php echo $cat['event_count']; ?> events</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Events Section -->
    <section class="featured-events">
        <div class="container">
            <div class="section-header">
                <h2>Featured Events</h2>
                <p>Handpicked events you don't want to miss</p>
                <a href="events.php" class="view-all">View All Events →</a>
            </div>
            <div class="events-grid">
                <?php if (count($featured_events) > 0): ?>
                    <?php foreach ($featured_events as $event): ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="assets/images/events/<?php echo $event['image'] ?? 'placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                     onerror="this.src='assets/images/event-placeholder.jpg'">
                                <span class="event-category"><?php echo htmlspecialchars($event['category_name'] ?? 'Uncategorized'); ?></span>
                                <?php if ($event['is_free']): ?>
                                    <span class="event-price-badge free">Free</span>
                                <?php else: ?>
                                    <span class="event-price-badge">₦<?php echo number_format($event['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="event-body">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-description"><?php echo substr(htmlspecialchars($event['description']), 0, 80) . '...'; ?></p>
                                <div class="event-meta">
                                    <span class="meta-item">
                                        <span class="icon"><i class="fas fa-calendar-alt"></i> </span> 
                                        <?php echo date('M d, Y', strtotime($event['start_date'])); ?>
                                    </span>
                                    <span class="meta-item">
                                        <span class="icon"><i class="fa-solid fa-location-dot"></i></span> 
                                        <?php echo htmlspecialchars($event['city']); ?>
                                    </span>
                                </div>
                                <div class="event-footer">
                                    <span class="tickets-available"><i class="fas fa-ticket-alt"></i> <?php echo $event['capacity'] - $event['tickets_sold']; ?> left</span>
                                    <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No featured events available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="upcoming-events">
        <div class="container">
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <p>Don't miss out on these exciting events happening soon</p>
            </div>
            <div class="upcoming-grid">
                <?php if (count($upcoming_events) > 0): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="upcoming-card">
                            <div class="upcoming-date">
                                <span class="day"><?php echo date('d', strtotime($event['start_date'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($event['start_date'])); ?></span>
                            </div>
                            <div class="upcoming-info">
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p><span class="icon"><i class="fa-solid fa-location-dot"></i></span> <?php echo htmlspecialchars($event['venue']); ?></p>
                                <p><span class="icon"><i class="fa-regular fa-clock"></i></span> 
                                    <?php echo date('g:i A', strtotime($event['start_date'])) . ' - ' . date('g:i A', strtotime($event['end_date'])); ?>
                                </p>
                            </div>
                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-small">View</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming events at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose EventEase?</h2>
                <p>We make event discovery and booking simple and secure</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search-location" style="color: #4A90D9; font-size: 40px;"></i>
                    </div>
                    <h3>Easy Discovery</h3>
                    <p>Find events that match your interests with our smart search and filtering system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card" style="color: #27AE60; font-size: 40px;"></i>
                    </div>
                    <h3>Secure Booking</h3>
                    <p>Book tickets safely with multiple payment options and instant confirmation.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt" style="color: #F39C12; font-size: 40px;"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access events and book tickets on the go with our responsive mobile design.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-cog" style="color: #8E44AD; font-size: 40px;"></i>
                    </div>
                    <h3>Personalized</h3>
                    <p>Get personalized event recommendations based on your interests and history.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Create Your Own Event?</h2>
                <p>Join thousands of organizers who trust EventEase to manage their events</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="create-event.php" class="btn btn-primary">Create Event</a>
                <?php else: ?>
                    <a href="signup.php" class="btn btn-primary">Get Started Free</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        // Hamburger menu toggle
        document.querySelector('.hamburger').addEventListener('click', function() {
            this.classList.toggle('active');
            document.querySelector('.nav-menu').classList.toggle('active');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animation on scroll (Intersection Observer)
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);
        document.querySelectorAll('.event-card, .feature-card, .category-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>