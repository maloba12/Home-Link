<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$pageTitle = 'Browse Properties - HomeLink';

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

include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-building"></i> Browse Properties</h1>
        <p>Find your perfect home from our extensive listings</p>
    </div>
</div>

<div class="container">
    <!-- Search Filters -->
    <div class="search-filters">
        <form method="GET" action="" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" placeholder="Search by location, title..." value="<?php echo htmlspecialchars($search); ?>">
                <input type="text" name="city" placeholder="City" value="<?php echo htmlspecialchars($city); ?>">
                
                <select name="type">
                    <option value="">All Types</option>
                    <option value="rent" <?php echo $type === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                    <option value="sale" <?php echo $type === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                </select>
                
                <select name="property_type">
                    <option value="">Property Type</option>
                    <option value="apartment" <?php echo $propertyType === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                    <option value="house" <?php echo $propertyType === 'house' ? 'selected' : ''; ?>>House</option>
                    <option value="condo" <?php echo $propertyType === 'condo' ? 'selected' : ''; ?>>Condo</option>
                    <option value="townhouse" <?php echo $propertyType === 'townhouse' ? 'selected' : ''; ?>>Townhouse</option>
                    <option value="studio" <?php echo $propertyType === 'studio' ? 'selected' : ''; ?>>Studio</option>
                </select>
            </div>
            
            <div class="filter-row">
                <input type="number" name="min_price" placeholder="Min Price (K)" value="<?php echo htmlspecialchars($minPrice); ?>">
                <input type="number" name="max_price" placeholder="Max Price (K)" value="<?php echo htmlspecialchars($maxPrice); ?>">
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="properties.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="properties-section">
        <h2 class="section-title">
            <i class="fas fa-list"></i> Available Properties 
            <span class="property-count">(<?php echo count($properties); ?> found)</span>
        </h2>
        
        <?php if (empty($properties)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>No properties found matching your criteria.</p>
                <a href="properties.php" class="btn btn-primary">View All Properties</a>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <?php
                        $primaryImage = $property['primary_image'] ?? '';
                        if ($primaryImage && strpos($primaryImage, 'assets/') !== 0 && strpos($primaryImage, '/') !== 0) {
                            $primaryImage = 'assets/images/' . ltrim($primaryImage, '/');
                        }
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
                                        <i class="far fa-heart"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
