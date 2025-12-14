<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$pageTitle = 'Home - HomeLink';
$page = 'home';

// Get search parameters
$search = $_GET['search'] ?? '';
$city = $_GET['city'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$type = $_GET['type'] ?? '';
$propertyType = $_GET['property_type'] ?? '';

// Build query
$sql = "SELECT p.*, u.username as seller_name, 
        (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
        FROM properties p 
        LEFT JOIN users u ON p.seller_id = u.user_id 
        WHERE p.status = 'approved'";

$params = [];

if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($city)) {
    $sql .= " AND p.city LIKE ?";
    $params[] = "%$city%";
}

if (!empty($minPrice)) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if (!empty($maxPrice)) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

if (!empty($type)) {
    $sql .= " AND p.type = ?";
    $params[] = $type;
}

if (!empty($propertyType)) {
    $sql .= " AND p.property_type = ?";
    $params[] = $propertyType;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Get cities for filter
$cityStmt = $pdo->query("SELECT DISTINCT city FROM properties WHERE status = 'approved' ORDER BY city");
$cities = $cityStmt->fetchAll();

include 'includes/header.php';
?>

<div class="hero-section">
    <div class="hero-content">
        <h1><i class="fas fa-home"></i> Find Your Perfect Home</h1>
        <p>Connect with properties that match your lifestyle</p>
        
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Search by location..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="text" name="city" placeholder="City" value="<?php echo htmlspecialchars($city); ?>">
            
            <select name="type">
                <option value="">All Types</option>
                <option value="rent" <?php echo $type === 'rent' ? 'selected' : ''; ?>>Rent</option>
                <option value="sale" <?php echo $type === 'sale' ? 'selected' : ''; ?>>Sale</option>
            </select>
            
            <select name="property_type">
                <option value="">All Property Types</option>
                <option value="apartment" <?php echo $propertyType === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                <option value="house" <?php echo $propertyType === 'house' ? 'selected' : ''; ?>>House</option>
                <option value="condo" <?php echo $propertyType === 'condo' ? 'selected' : ''; ?>>Condo</option>
                <option value="townhouse" <?php echo $propertyType === 'townhouse' ? 'selected' : ''; ?>>Townhouse</option>
                <option value="studio" <?php echo $propertyType === 'studio' ? 'selected' : ''; ?>>Studio</option>
            </select>
            
            <input type="number" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($minPrice); ?>">
            <input type="number" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($maxPrice); ?>">
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</div>

<div class="container">
    <div class="properties-section">
        <h2 class="section-title">
            <i class="fas fa-building"></i> Available Properties 
            <span class="property-count">(<?php echo count($properties); ?>)</span>
        </h2>
        
        <?php if (empty($properties)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>No properties found. Try adjusting your search criteria.</p>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($properties as $property): ?>
                    <div class="property-card" data-property-id="<?php echo $property['property_id']; ?>">
                        <?php
                        $primaryImage = $property['primary_image'] ?? '';
                        // Normalize to assets/images if only filename stored
                        if ($primaryImage && strpos($primaryImage, 'assets/') !== 0 && strpos($primaryImage, '/') !== 0) {
                            $primaryImage = 'assets/images/' . ltrim($primaryImage, '/');
                        }
                        // Verify file exists on disk; if not, treat as no image
                        $hasImage = false;
                        if (!empty($primaryImage)) {
                            $fsPath = __DIR__ . '/' . ltrim($primaryImage, '/');
                            if (file_exists($fsPath)) {
                                $hasImage = true;
                            }
                        }
                        ?>
                        <?php if ($hasImage): ?>
                            <img src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
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
                                <?php if ($property['sqft']): ?>
                                    <span><i class="fas fa-ruler-combined"></i> <?php echo $property['sqft']; ?> sqft</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="property-price">
                                <strong>K<?php echo number_format($property['price']); ?></strong>
                                <span class="property-type-badge"><?php echo ucfirst($property['type']); ?></span>
                            </div>
                            
                            <div class="property-actions">
                                <a href="property_details.php?id=<?php echo $property['property_id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-favorite" data-property-id="<?php echo $property['property_id']; ?>">
                                        <i class="far fa-heart"></i> Save
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (isLoggedIn()): ?>
        <div id="smart-recommendations" class="recommendations-section" style="display: none;">
            <h2 class="section-title">
                <i class="fas fa-magic"></i> Smart Recommendations
            </h2>
            <div id="recommendations-container" class="properties-grid"></div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

