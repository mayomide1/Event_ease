<?php
session_start();

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Sample events data (will be replaced with database later)
$featured_events = [
    [
        'id' => 1,
        'title' => 'Tech Conference 2026',
        'category' => 'Conference',
        'date' => 'June 20, 2026',
        'time' => '9:00 AM - 6:00 PM',
        'venue' => 'Lagos Convention Center',
        'city' => 'Lagos',
        'price' => '₦15,000',
        'image' => 'tech-conference.jpg',
        'description' => 'Join industry leaders for the biggest tech event of the year. Network, learn, and innovate!',
        'available_tickets' => 150
    ],
    [
        'id' => 2,
        'title' => 'Music Festival 2026',
        'category' => 'Concert',
        'date' => 'July 15, 2026',
        'time' => '4:00 PM - 11:59 PM',
        'venue' => 'Eko Atlantic City',
        'city' => 'Lagos',
        'price' => '₦25,000',
        'image' => 'music-festival.jpg',
        'description' => 'Experience the best of Nigerian music with top artists performing live!',
        'available_tickets' => 300
    ],
    [
        'id' => 3,
        'title' => 'Entrepreneurship Workshop',
        'category' => 'Workshop',
        'date' => 'August 5, 2026',
        'time' => '10:00 AM - 4:00 PM',
        'venue' => 'Business Hub',
        'city' => 'Abuja',
        'price' => '₦10,000',
        'image' => 'workshop.jpg',
        'description' => 'Learn essential business skills from successful entrepreneurs and industry experts.',
        'available_tickets' => 50
    ],
    [
        'id' => 4,
        'title' => 'Charity Gala Night',
        'category' => 'Charity',
        'date' => 'September 10, 2026',
        'time' => '6:00 PM - 10:00 PM',
        'venue' => 'Grand Ballroom',
        'city' => 'Port Harcourt',
        'price' => 'Free',
        'image' => 'charity-gala.jpg',
        'description' => 'Join us for a night of elegance and giving back to the community.',
        'available_tickets' => 200
    ],
    [
        'id' => 5,
        'title' => 'Sports Tournament',
        'category' => 'Sports',
        'date' => 'October 2, 2026',
        'time' => '8:00 AM - 6:00 PM',
        'venue' => 'National Stadium',
        'city' => 'Abuja',
        'price' => '₦5,000',
        'image' => 'sports-tournament.jpg',
        'description' => 'Annual sports tournament featuring football, basketball, and athletics.',
        'available_tickets' => 500
    ],
    [
        'id' => 6,
        'title' => 'Art Exhibition',
        'category' => 'Exhibition',
        'date' => 'November 12, 2026',
        'time' => '10:00 AM - 8:00 PM',
        'venue' => 'Art Gallery',
        'city' => 'Lagos',
        'price' => '₦7,500',
        'image' => 'art-exhibition.jpg',
        'description' => 'Showcasing the best of contemporary African art and emerging artists.',
        'available_tickets' => 80
    ]
];

$upcoming_events = array_slice($featured_events, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventEase - Find & Book Events</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/homepage.css">
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
                    <span class="number">500+</span>
                    <span class="label"><i class="fas fa-calendar-check"></i> Events Hosted</span>
                </div>
                <div class="stat">
                    <span class="number">10K+</span>
                    <span class="label"><i class="fas fa-users"></i> Happy Attendees</span>
                </div>
                <div class="stat">
                    <span class="number">50+</span>
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
        <a href="events.php?category=conference" class="category-card">
            <span class="icon"><i class="fas fa-briefcase" style="color: #4A90D9;"></i></span>
            <h3>Conferences</h3>
            <p>150+ events</p>
        </a>
        <a href="events.php?category=concert" class="category-card">
            <span class="icon"><i class="fas fa-music" style="color: #E74C3C;"></i></span>
            <h3>Concerts</h3>
            <p>120+ events</p>
        </a>
        <a href="events.php?category=workshop" class="category-card">
            <span class="icon"><i class="fas fa-tools" style="color: #F39C12;"></i></span>
            <h3>Workshops</h3>
            <p>80+ events</p>
        </a>
        <a href="events.php?category=sports" class="category-card">
            <span class="icon"><i class="fas fa-futbol" style="color: #27AE60;"></i></span>
            <h3>Sports</h3>
            <p>60+ events</p>
        </a>
        <a href="events.php?category=charity" class="category-card">
            <span class="icon"><i class="fas fa-heart" style="color: #E74C3C;"></i></span>
            <h3>Charity</h3>
            <p>40+ events</p>
        </a>
        <a href="events.php?category=exhibition" class="category-card">
            <span class="icon"><i class="fas fa-palette" style="color: #8E44AD;"></i></span>
            <h3>Exhibitions</h3>
            <p>35+ events</p>
        </a>
    </div>
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
                <?php foreach ($featured_events as $event): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <img src="assets/images/events/<?php echo $event['image']; ?>" 
                                 alt="<?php echo $event['title']; ?>" 
                                 onerror="this.src='assets/images/event-placeholder.jpg'">
                            <span class="event-category"><?php echo $event['category']; ?></span>
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
                                    <span class="icon"><i class="fas fa-calendar-alt"></i> </span> <?php echo $event['date']; ?>
                                </span>
                                <span class="meta-item">
                                    <span class="icon"><i class="fa-solid fa-location-dot"></i></i></span> <?php echo $event['city']; ?>
                                </span>
                            </div>
                            <div class="event-footer">
                                <span class="tickets-available"><i class="fas fa-ticket-alt"></i>  <?php echo $event['available_tickets']; ?> left</span>
                                <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-book">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
                <?php foreach ($upcoming_events as $event): ?>
                    <div class="upcoming-card">
                        <div class="upcoming-date">
                            <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                            <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                        </div>
                        <div class="upcoming-info">
                            <h4><?php echo $event['title']; ?></h4>
                            <p><span class="icon"><i class="fa-solid fa-location-dot"></i></span> <?php echo $event['venue']; ?></p>
                            <p><span class="icon"><i class="fa-regular fa-clock"></i></span> <?php echo $event['time']; ?></p>
                        </div>
                        <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-small">View</a>
                    </div>
                <?php endforeach; ?>
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3><i class="fa-solid fa-ticket"></i> EventEase</h3>
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
                        <li><a href="events.php">Events</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="help.php">Help Center</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> EventEase. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.querySelector('.hamburger').addEventListener('click', function() {
            this.classList.toggle('active');
            document.querySelector('.nav-menu').classList.toggle('active');
        });

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