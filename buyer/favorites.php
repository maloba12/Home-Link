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

$pageTitle = 'My Favorites - HomeLink';
$userId = $_SESSION['user_id'];

// Handle remove from favorites
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE favorite_id = ? AND user_id = ?");
        $stmt->execute([$_GET['remove'], $userId]);
        header('Location: /buyer/favorites.php?success=removed');
        exit();
    } catch (PDOException $e) {
        $error = "Failed to remove favorite";
    }
}

// Get all favorite properties
try {
    $favoritesStmt = $pdo->prepare("SELECT f.favorite_id, p.*, u.username as seller_name, u.email as seller_email,
                                    (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
                                    FROM favorites f
                                    JOIN properties p ON f.property_id = p.property_id
                                    JOIN users u ON p.seller_id = u.user_id
                                    WHERE f.user_id = ?
                                    ORDER BY f.created_at DESC");
    $favoritesStmt->execute([$userId]);
    $favoriteProperties = $favoritesStmt->fetchAll();
} catch (PDOException $e) {
    $favoriteProperties = [];
    $error = "Could not load favorites";
}

include 'buyer_header.php';
?>

<div class="admin-layout">
    <?php include 'buyer_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-heart"></i> My Favorite Properties</h1>
            <p>Properties you've saved for later viewing</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Property removed from favorites!
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Saved Properties (<?php echo count($favoriteProperties); ?>)</h2>
                <a href="/properties.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Browse More Properties
                </a>
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
                                    <span><i class="fas fa-ruler-combined"></i> <?php echo number_format($property['area']); ?> sqft</span>
                                </div>
                                
                                <p class="property-description">
                                    <?php echo htmlspecialchars(substr($property['description'], 0, 100)) . '...'; ?>
                                </p>
                                
                                <div class="property-price">
                                    <strong>K<?php echo number_format($property['price']); ?></strong>
                                    <span class="property-type-badge"><?php echo ucfirst($property['type']); ?></span>
                                </div>
                                
                                <div class="property-seller">
                                    <i class="fas fa-user"></i> Listed by: <strong><?php echo htmlspecialchars($property['seller_name']); ?></strong>
                                </div>
                                
                                <div class="property-actions">
                                    <a href="/property_details.php?id=<?php echo $property['property_id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <a href="/buyer/favorites.php?remove=<?php echo $property['favorite_id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Remove this property from favorites?')">
                                        <i class="fas fa-heart-broken"></i> Remove
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
