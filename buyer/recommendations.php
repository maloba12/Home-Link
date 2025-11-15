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

$pageTitle = 'Recommendations - HomeLink';
$userId = $_SESSION['user_id'];

// Get recommended properties (properties not in favorites, approved status)
try {
    $recommendedStmt = $pdo->prepare("SELECT DISTINCT p.*, u.username as seller_name,
                                      (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
                                      FROM properties p
                                      JOIN users u ON p.seller_id = u.user_id
                                      WHERE p.status = 'approved'
                                      AND p.property_id NOT IN (
                                          SELECT property_id FROM favorites WHERE user_id = ?
                                      )
                                      ORDER BY p.created_at DESC
                                      LIMIT 12");
    $recommendedStmt->execute([$userId]);
    $recommendedProperties = $recommendedStmt->fetchAll();
} catch (PDOException $e) {
    $recommendedProperties = [];
    $error = "Could not load recommendations";
}

include 'buyer_header.php';
?>

<div class="admin-layout">
    <?php include 'buyer_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-magic"></i> Recommended For You</h1>
            <p>Properties we think you'll love based on your preferences</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-star"></i> Smart Matches (<?php echo count($recommendedProperties); ?>)</h2>
                <a href="/properties.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Browse All Properties
                </a>
            </div>
            
            <?php if (empty($recommendedProperties)): ?>
                <div class="empty-state">
                    <i class="fas fa-magic"></i>
                    <p>No recommendations available at the moment.</p>
                    <a href="/properties.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Properties
                    </a>
                </div>
            <?php else: ?>
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
                                    <span><i class="fas fa-ruler-combined"></i> <?php echo number_format($property['area']); ?> sqft</span>
                                </div>
                                
                                <p class="property-description">
                                    <?php echo htmlspecialchars(substr($property['description'], 0, 100)) . '...'; ?>
                                </p>
                                
                                <div class="property-price">
                                    <strong>K<?php echo number_format($property['price']); ?></strong>
                                    <span class="property-type-badge"><?php echo ucfirst($property['type']); ?></span>
                                </div>
                                
                                <div class="property-actions">
                                    <a href="/property_details.php?id=<?php echo $property['property_id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <button class="btn btn-secondary btn-favorite" data-property-id="<?php echo $property['property_id']; ?>">
                                        <i class="far fa-heart"></i> Save
                                    </button>
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
