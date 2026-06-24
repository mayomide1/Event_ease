<?php 
    $current_page = basename($_SERVER['PHP_SELF']);
?>
    <div class="footer">
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
                    <form action="#" method="POST" class="newsletter-form">
                        <input type="email" name="email" placeholder="Enter your email" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> EventEase. All rights reserved.</p>
            </div>
        </div>
    </div>