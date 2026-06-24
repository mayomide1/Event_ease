<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$current_page = basename($_SERVER['PHP_SELF']);
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

$db = Database::getConnection();

// ---------- Build the WHERE clause ----------
$where = "WHERE e.status = 'published'";
$params = [];

if ($selected_category) {
    $where .= " AND c.slug = :category";
    $params[':category'] = $selected_category;
}

// ---------- Count total events (for pagination) ----------
$count_sql = "
    SELECT COUNT(*) as total
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    LEFT JOIN users u ON e.organizer_id = u.id
    $where
";
$stmt = $db->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_events = $stmt->fetch()['total'] ?? 0;
$total_pages = ceil($total_events / 6);

$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page_num = max(1, min($current_page_num, $total_pages));
$offset = ($current_page_num - 1) * 6;

// ---------- Main query with pagination ----------
$sql = "
    SELECT e.*, 
           c.name as category_name, 
           c.slug as category_slug, 
           u.name as organizer_name
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    LEFT JOIN users u ON e.organizer_id = u.id
    $where
    ORDER BY e.start_date ASC
    LIMIT :limit OFFSET :offset
";
$params[':limit'] = 6;
$params[':offset'] = $offset;

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    if ($key === ':limit' || $key === ':offset') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();
$events = $stmt->fetchAll();

// ---------- Get categories with published events ----------
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
    <link rel="stylesheet" href="assets/css/events.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
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

    <?php include 'footer.php'; ?>
</body>
</html>