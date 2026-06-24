<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Get category filter from URL
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

$db = Database::getConnection();

// ---------- Build the base query for published events ----------
$sql = "
    SELECT e.*, 
           c.name as category_name, 
           c.slug as category_slug, 
           u.name as organizer_name
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    LEFT JOIN users u ON e.organizer_id = u.id
    WHERE e.status = 'published'
";
$params = [];

// Apply category filter
if ($selected_category) {
    $sql .= " AND c.slug = ?";
    $params[] = $selected_category;
}

// Order by start date (upcoming first)
$sql .= " ORDER BY e.start_date ASC";

// ---------- Pagination ----------
$events_per_page = 6;

// Get total count for pagination
$count_sql = str_replace("SELECT e.*, c.name as category_name, c.slug as category_slug, u.name as organizer_name", "SELECT COUNT(*) as total", $sql);
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_events = $stmt->fetch()['total'] ?? 0;
$total_pages = ceil($total_events / $events_per_page);

$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page_num = max(1, min($current_page_num, $total_pages));
$offset = ($current_page_num - 1) * $events_per_page;

// Add LIMIT and OFFSET with proper integer binding
$sql .= " LIMIT :limit OFFSET :offset";

// Execute query with proper integer binding
$stmt = $db->prepare($sql);

// Bind the category parameter if it exists
foreach ($params as $key => $value) {
    $stmt->bindValue($key + 1, $value);
}

$stmt->bindValue(':limit', $events_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll();

// Get all categories (for filter buttons)
$cat_stmt = $db->query("
    SELECT DISTINCT c.id, c.name, c.slug
    FROM categories c
    JOIN events e ON e.category_id = c.id
    WHERE e.status = 'published'
    ORDER BY c.name
");
$categories = $cat_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selected_category ? ucfirst($selected_category) . ' Events' : 'All Events'; ?> - EventEase</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/events.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
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
                        <a href="events.php?category=<?php echo $cat['slug']; ?>" 
                           class="filter-btn <?php echo strtolower($selected_category) === $cat['slug'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
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
            <?php if (count($events) > 0): ?>
                <div class="events-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card" data-category="<?php echo $event['category_slug']; ?>">
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
                                        <span class="icon"><i class="fas fa-calendar-alt"></i></span> 
                                        <?php echo date('M d, Y', strtotime($event['start_date'])); ?>
                                    </span>
                                    <span class="meta-item">
                                        <span class="icon"><i class="fa-solid fa-location-dot"></i></span> 
                                        <?php echo htmlspecialchars($event['city']); ?>
                                    </span>
                                </div>
                                <div class="event-organizer">
                                    <span class="organizer-label">Organized by:</span>
                                    <span class="organizer-name"><?php echo htmlspecialchars($event['organizer_name']); ?></span>
                                </div>
                                <div class="event-footer">
                                    <span class="tickets-available"><i class="fas fa-ticket-alt"></i> <?php echo $event['capacity'] - $event['tickets_sold']; ?> left</span>
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
    <?php include 'footer.php'; ?>

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