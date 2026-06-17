<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

// Get category filter from URL
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// Sample events data (will be replaced with database later)
$all_events = [
    [
        'id' => 1,
        'title' => 'Tech Conference 2026',
        'category' => 'conference',
        'date' => 'June 20, 2026',
        'time' => '9:00 AM - 6:00 PM',
        'venue' => 'Lagos Convention Center',
        'city' => 'Lagos',
        'price' => '₦15,000',
        'image' => 'tech-conference.jpg',
        'description' => 'Join industry leaders for the biggest tech event of the year. Network, learn, and innovate!',
        'available_tickets' => 150,
        'organizer' => 'TechHub NG'
    ],
    [
        'id' => 2,
        'title' => 'Music Festival 2026',
        'category' => 'concert',
        'date' => 'July 15, 2026',
        'time' => '4:00 PM - 11:59 PM',
        'venue' => 'Eko Atlantic City',
        'city' => 'Lagos',
        'price' => '₦25,000',
        'image' => 'music-festival.jpg',
        'description' => 'Experience the best of Nigerian music with top artists performing live!',
        'available_tickets' => 300,
        'organizer' => 'Live Music NG'
    ],
    [
        'id' => 3,
        'title' => 'Entrepreneurship Workshop',
        'category' => 'workshop',
        'date' => 'August 5, 2026',
        'time' => '10:00 AM - 4:00 PM',
        'venue' => 'Business Hub',
        'city' => 'Abuja',
        'price' => '₦10,000',
        'image' => 'workshop.jpg',
        'description' => 'Learn essential business skills from successful entrepreneurs and industry experts.',
        'available_tickets' => 50,
        'organizer' => 'BizAcademy'
    ],
    [
        'id' => 4,
        'title' => 'Charity Gala Night',
        'category' => 'charity',
        'date' => 'September 10, 2026',
        'time' => '6:00 PM - 10:00 PM',
        'venue' => 'Grand Ballroom',
        'city' => 'Port Harcourt',
        'price' => 'Free',
        'image' => 'charity-gala.jpg',
        'description' => 'Join us for a night of elegance and giving back to the community.',
        'available_tickets' => 200,
        'organizer' => 'Charity Foundation'
    ],
    [
        'id' => 5,
        'title' => 'Sports Tournament',
        'category' => 'sports',
        'date' => 'October 2, 2026',
        'time' => '8:00 AM - 6:00 PM',
        'venue' => 'National Stadium',
        'city' => 'Abuja',
        'price' => '₦5,000',
        'image' => 'sports-tournament.jpg',
        'description' => 'Annual sports tournament featuring football, basketball, and athletics.',
        'available_tickets' => 500,
        'organizer' => 'Sports Federation'
    ],
    [
        'id' => 6,
        'title' => 'Art Exhibition',
        'category' => 'exhibition',
        'date' => 'November 12, 2026',
        'time' => '10:00 AM - 8:00 PM',
        'venue' => 'Art Gallery',
        'city' => 'Lagos',
        'price' => '₦7,500',
        'image' => 'art-exhibition.jpg',
        'description' => 'Showcasing the best of contemporary African art and emerging artists.',
        'available_tickets' => 80,
        'organizer' => 'Art Collective'
    ],
    [
        'id' => 7,
        'title' => 'Web Development Bootcamp',
        'category' => 'workshop',
        'date' => 'December 5, 2026',
        'time' => '9:00 AM - 5:00 PM',
        'venue' => 'Innovation Hub',
        'city' => 'Lagos',
        'price' => '₦20,000',
        'image' => 'bootcamp.jpg',
        'description' => 'Intensive 5-day bootcamp covering HTML, CSS, JavaScript, and PHP.',
        'available_tickets' => 30,
        'organizer' => 'Code Academy'
    ],
    [
        'id' => 8,
        'title' => 'Jazz Night',
        'category' => 'concert',
        'date' => 'January 20, 2027',
        'time' => '7:00 PM - 10:00 PM',
        'venue' => 'Jazz Lounge',
        'city' => 'Abuja',
        'price' => '₦8,000',
        'image' => 'jazz-night.jpg',
        'description' => 'An evening of smooth jazz with Nigeria\'s finest jazz musicians.',
        'available_tickets' => 100,
        'organizer' => 'Jazz Club NG'
    ]
];

// Filter events by category if selected
if ($selected_category) {
    $filtered_events = array_filter($all_events, function($event) use ($selected_category) {
        return strtolower($event['category']) === strtolower($selected_category);
    });
} else {
    $filtered_events = $all_events;
}

// Get unique categories for filter buttons
$categories = array_unique(array_column($all_events, 'category'));

// Pagination (simple example)
$events_per_page = 6;
$total_events = count($filtered_events);
$total_pages = ceil($total_events / $events_per_page);
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page_num = max(1, min($current_page_num, $total_pages));
$offset = ($current_page_num - 1) * $events_per_page;
$paginated_events = array_slice($filtered_events, $offset, $events_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selected_category ? ucfirst($selected_category) . ' Events' : 'All Events'; ?> - EventEase</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/events.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
      crossorigin="anonymous" 
      referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php" class="logo"><i class="fa-solid fa-ticket"></i> EventEase</a>
            </div>
            
            <ul class="nav-menu">
                <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                    <li><a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="signup.php" class="btn-signup"><i class="fas fa-user-plus"></i> Sign Up</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1><?php echo $selected_category ? ucfirst($selected_category) . ' Events' : 'All Events'; ?></h1>
            <p><?php echo $total_events; ?> events found</p>
        </div>
    </section>

    <!-- Filter & Search -->
    <section class="filter-section">
        <div class="container">
            <div class="filter-bar">
                <div class="filter-categories">
                    <a href="events.php" class="filter-btn <?php echo !$selected_category ? 'active' : ''; ?>">All</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="events.php?category=<?php echo strtolower($cat); ?>" 
                           class="filter-btn <?php echo strtolower($selected_category) === strtolower($cat) ? 'active' : ''; ?>">
                            <?php echo ucfirst($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="filter-search">
                    <input type="text" id="searchEvents" placeholder="Search events..." class="search-input">
                </div>
            </div>
        </div>
    </section>

    <!-- Events Grid -->
    <section class="events-section">
        <div class="container">
            <?php if (count($paginated_events) > 0): ?>
                <div class="events-grid">
                    <?php foreach ($paginated_events as $event): ?>
                        <div class="event-card" data-category="<?php echo $event['category']; ?>">
                            <div class="event-image">
                                <img src="assets/images/events/<?php echo $event['image']; ?>" 
                                     alt="<?php echo $event['title']; ?>" 
                                     onerror="this.src='assets/images/event-placeholder.jpg'">
                                <span class="event-category"><?php echo ucfirst($event['category']); ?></span>
                                <?php if ($event['price'] == 'Free'): ?>
                                    <span class="event-price-badge free">Free</span>
                                <?php else: ?>
                                    <span class="event-price-badge"><?php echo $event['price']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="event-body">
                                <h3><?php echo $event['title']; ?></h3>
                                <p class="event-description"><?php echo substr($event['description'], 0, 80) . '...'; ?></p>
                                <div class="event-meta">
                                    <span class="meta-item">
                                        <span class="icon">📅</span> <?php echo $event['date']; ?>
                                    </span>
                                    <span class="meta-item">
                                        <span class="icon">📍</span> <?php echo $event['city']; ?>
                                    </span>
                                </div>
                                <div class="event-organizer">
                                    <span class="organizer-label">Organized by:</span>
                                    <span class="organizer-name"><?php echo $event['organizer']; ?></span>
                                </div>
                                <div class="event-footer">
                                    <span class="tickets-available">🎟️ <?php echo $event['available_tickets']; ?> left</span>
                                    <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page_num > 1): ?>
                            <a href="?page=<?php echo $current_page_num - 1; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?>" class="page-link">← Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?>" 
                               class="page-link <?php echo $i === $current_page_num ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page_num < $total_pages): ?>
                            <a href="?page=<?php echo $current_page_num + 1; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?>" class="page-link">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-events">
                    <div class="no-events-icon">🔍</div>
                    <h3>No events found</h3>
                    <p>Try adjusting your search or filter to find what you're looking for.</p>
                    <a href="events.php" class="btn-primary">View All Events</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>🎫 EventEase</h3>
                    <p>Your trusted platform for discovering and booking amazing events.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fa-solid fa-envelope"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="events.php">All Events</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Categories</h4>
                    <ul>
                        <?php foreach ($categories as $cat): ?>
                            <li><a href="events.php?category=<?php echo strtolower($cat); ?>"><?php echo ucfirst($cat); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-newsletter">
                    <h4>Stay Updated</h4>
                    <p>Subscribe to get updates on new events</p>
                    <form action="subscribe.php" method="POST" class="newsletter-form">
                        <input type="email" name="email" placeholder="Enter your email" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> EventEase. All rights reserved. | Made with ❤️ for campus events</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Hamburger Menu
        document.querySelector('.hamburger').addEventListener('click', function() {
            this.classList.toggle('active');
            document.querySelector('.nav-menu').classList.toggle('active');
        });

        // Search functionality
        const searchInput = document.getElementById('searchEvents');
        const eventCards = document.querySelectorAll('.event-card');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            eventCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('.event-description').textContent.toLowerCase();
                const category = card.dataset.category.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm) || category.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>