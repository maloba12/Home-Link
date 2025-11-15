<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit();
}

if (!isBuyer()) {
    header('Location: /index.php');
    exit();
}

$pageTitle = 'Buyer Dashboard - HomeLink';
$userId = $_SESSION['user_id'];

// Get buyer statistics
$stats = [];

// Total saved properties
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['saved_properties'] = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $stats['saved_properties'] = 0;
}

// Total bookings
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['total_bookings'] = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $stats['total_bookings'] = 0;
}

// Properties viewed (placeholder)
$stats['properties_viewed'] = 0;

// Get saved/favorite properties
try {
    $favoritesStmt = $pdo->prepare("SELECT p.*, u.username as seller_name,
                                    (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
                                    FROM favorites f
                                    JOIN properties p ON f.property_id = p.property_id
                                    JOIN users u ON p.seller_id = u.user_id
                                    WHERE f.user_id = ?
                                    ORDER BY f.created_at DESC
                                    LIMIT 6");
    $favoritesStmt->execute([$userId]);
    $favoriteProperties = $favoritesStmt->fetchAll();
} catch (PDOException $e) {
    $favoriteProperties = [];
}

// Get recent bookings
try {
    $bookingsStmt = $pdo->prepare("SELECT b.*, p.title as property_title, p.address, p.city, u.username as seller_name, u.email as seller_email
                                   FROM bookings b
                                   JOIN properties p ON b.property_id = p.property_id
                                   JOIN users u ON p.seller_id = u.user_id
                                   WHERE b.user_id = ?
                                   ORDER BY b.booking_date DESC
                                   LIMIT 5");
    $bookingsStmt->execute([$userId]);
    $recentBookings = $bookingsStmt->fetchAll();
} catch (PDOException $e) {
    $recentBookings = [];
}

// Get recommended properties
try {
    $recommendedStmt = $pdo->prepare("SELECT DISTINCT p.*, u.username as seller_name,
                                      (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
                                      FROM properties p
                                      JOIN users u ON p.seller_id = u.user_id
                                      WHERE p.status = 'approved'
                                      ORDER BY p.created_at DESC
                                      LIMIT 4");
    $recommendedStmt->execute();
    $recommendedProperties = $recommendedStmt->fetchAll();
} catch (PDOException $e) {
    $recommendedProperties = [];
}

include 'buyer_header.php';
?>

<div class="admin-layout">
    <?php include 'buyer_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-user"></i> Buyer Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Find your perfect home.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['saved_properties']; ?></h3>
                    <p>Saved Properties</p>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_bookings']; ?></h3>
                    <p>Bookings Made</p>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['properties_viewed']; ?></h3>
                    <p>Properties Viewed</p>
                </div>
            </div>

            <div class="stat-card stat-secondary">
                <div class="stat-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="stat-details">
                    <h3>Active</h3>
                    <p>Search Status</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-section">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="action-buttons-grid">
                <a href="/properties.php" class="action-btn btn-primary">
                    <i class="fas fa-search"></i>
                    <span>Browse Properties</span>
                </a>
                <a href="/buyer/favorites.php" class="action-btn btn-danger">
                    <i class="fas fa-heart"></i>
                    <span>My Favorites</span>
                </a>
                <a href="/buyer/bookings.php" class="action-btn btn-success">
                    <i class="fas fa-calendar"></i>
                    <span>My Bookings</span>
                </a>
                <a href="/profile.php" class="action-btn btn-secondary">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Profile</span>
                </a>
            </div>
        </div>

        <!-- Saved Properties -->
        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-heart"></i> Saved Properties</h2>
                <a href="/buyer/favorites.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            
            <?php if (empty($favoriteProperties)): ?>
                <div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <p>You haven't saved any properties yet.</p>
                    <a href="/properties.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Properties
                    </a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($favoriteProperties as $property): ?>
                        <div class="property-card">
                            <?php
                            $primaryImage = $property['primary_image'] ?? '';
                            if ($primaryImage && strpos($primaryImage, 'assets/') !== 0 && strpos($primaryImage, '/') !== 0) {
                                $primaryImage = 'assets/images/' . ltrim($primaryImage, '/');
                            }
                            $hasImage = false;
                            if (!empty($primaryImage)) {
                                $fsPath = dirname(__DIR__) . '/' . ltrim($primaryImage, '/');
                                if (file_exists($fsPath)) {
                                    $hasImage = true;
                                }
                            }
                            ?>
                            <?php if ($hasImage): ?>
                                <img src="<?php echo htmlspecialchars('/' . ltrim($primaryImage, '/')); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
                            <?php else: ?>
                                <div class="property-image-placeholder">
                                    <i class="fas fa-home"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="property-info">
                                <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p class="property-location">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($property['address'] . ', ' . $property['city']); ?>
                                </p>
                                
                                <div class="property-details">
                                    <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                                    <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                                </div>
                                
                                <div class="property-price">
                                    <strong>K<?php echo number_format($property['price']); ?></strong>
                                    <span class="property-type-badge"><?php echo ucfirst($property['type']); ?></span>
                                </div>
                                
                                <div class="property-actions">
                                    <a href="/property_details.php?id=<?php echo $property['property_id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Bookings -->
        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-check"></i> My Bookings</h2>
                <a href="/buyer/bookings.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            
            <?php if (empty($recentBookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar"></i>
                    <p>You haven't made any bookings yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Location</th>
                                <th>Seller</th>
                                <th>Contact</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['property_title']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['city']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['seller_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['seller_email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recommended Properties -->
        <?php if (!empty($recommendedProperties)): ?>
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-magic"></i> Recommended For You</h2>
                    <a href="/properties.php" class="btn btn-sm btn-primary">View More</a>
                </div>
                
                <div class="properties-grid">
                    <?php foreach ($recommendedProperties as $property): ?>
                        <div class="property-card">
                            <?php
                            $primaryImage = $property['primary_image'] ?? '';
                            if ($primaryImage && strpos($primaryImage, 'assets/') !== 0 && strpos($primaryImage, '/') !== 0) {
                                $primaryImage = 'assets/images/' . ltrim($primaryImage, '/');
                            }
                            $hasImage = false;
                            if (!empty($primaryImage)) {
                                $fsPath = dirname(__DIR__) . '/' . ltrim($primaryImage, '/');
                                if (file_exists($fsPath)) {
                                    $hasImage = true;
                                }
                            }
                            ?>
                            <?php if ($hasImage): ?>
                                <img src="<?php echo htmlspecialchars('/' . ltrim($primaryImage, '/')); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
                            <?php else: ?>
                                <div class="property-image-placeholder">
                                    <i class="fas fa-home"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="property-info">
                                <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p class="property-location">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($property['address'] . ', ' . $property['city']); ?>
                                </p>
                                
                                <div class="property-details">
                                    <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                                    <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                                </div>
                                
                                <div class="property-price">
                                    <strong>K<?php echo number_format($property['price']); ?></strong>
                                    <span class="property-type-badge"><?php echo ucfirst($property['type']); ?></span>
                                </div>
                                
                                <div class="property-actions">
                                    <a href="/property_details.php?id=<?php echo $property['property_id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <button class="btn btn-favorite" data-property-id="<?php echo $property['property_id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
