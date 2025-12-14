<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$property_id = $_GET['id'] ?? 0;

if (!$property_id) {
    header('Location: index.php');
    exit();
}

// Get property details
$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name, u.email as seller_email, u.phone as seller_phone 
                       FROM properties p 
                       LEFT JOIN users u ON p.seller_id = u.user_id 
                       WHERE p.property_id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: index.php');
    exit();
}

// Get property images
$imageStmt = $pdo->prepare("SELECT * FROM images WHERE property_id = ? ORDER BY is_primary DESC");
$imageStmt->execute([$property_id]);
$images = $imageStmt->fetchAll();

// Get amenities
$amenityStmt = $pdo->prepare("SELECT amenity FROM amenities WHERE property_id = ?");
$amenityStmt->execute([$property_id]);
$amenities = $amenityStmt->fetchAll(PDO::FETCH_COLUMN);

// Check if favorite
$isFavorite = false;
if (isLoggedIn()) {
    $favStmt = $pdo->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND property_id = ?");
    $favStmt->execute([getUserId(), $property_id]);
    $isFavorite = (bool) $favStmt->fetch();
}

$pageTitle = $property['title'] . ' - HomeLink';
include 'includes/header.php';
?>

<div class="container">
    <div class="property-details">
        <div class="property-images">
            <?php if (!empty($images)): ?>
                <?php 
                // Resolve URL for main image and verify file exists
                $mainUrl = $images[0]['image_url'] ?? '';
                if ($mainUrl && strpos($mainUrl, 'assets/') !== 0 && strpos($mainUrl, '/') !== 0) {
                    $mainUrl = 'assets/images/' . ltrim($mainUrl, '/');
                }
                $mainExists = false;
                if (!empty($mainUrl)) {
                    $mainFs = __DIR__ . '/' . ltrim($mainUrl, '/');
                    if (file_exists($mainFs)) {
                        $mainExists = true;
                    }
                }
                ?>
                <div class="main-image">
                    <?php if (!empty($mainUrl) && $mainExists): ?>
                        <img src="<?php echo htmlspecialchars($mainUrl); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" id="main-img">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-home"></i>
                            <p>No image available</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="image-thumbnails">
                        <?php foreach ($images as $img): ?>
                            <?php 
                            $thumbUrl = $img['image_url'] ?? '';
                            if ($thumbUrl && strpos($thumbUrl, 'assets/') !== 0 && strpos($thumbUrl, '/') !== 0) {
                                $thumbUrl = 'assets/images/' . ltrim($thumbUrl, '/');
                            }
                            $thumbExists = false;
                            if (!empty($thumbUrl)) {
                                $thumbFs = __DIR__ . '/' . ltrim($thumbUrl, '/');
                                if (file_exists($thumbFs)) {
                                    $thumbExists = true;
                                }
                            }
                            ?>
                            <?php if (!empty($thumbUrl) && $thumbExists): ?>
                                <img src="<?php echo htmlspecialchars('/' . ltrim($thumbUrl, '/')); ?>" 
                                     alt="Thumbnail" 
                                     class="thumbnail <?php echo !empty($img['is_primary']) ? 'active' : ''; ?>"
                                     onclick="changeMainImage(this)">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-image">
                    <i class="fas fa-home"></i>
                    <p>No images available</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="property-info-section">
            <div class="property-header">
                <h1><?php echo htmlspecialchars($property['title']); ?></h1>
                <div class="property-price-big">
                    K<?php echo number_format($property['price']); ?>
                    <span class="property-type-badge"><?php echo ucfirst($property['type']); ?></span>
                </div>
            </div>
            
            <p class="property-location">
                <i class="fas fa-map-marker-alt"></i> 
                <?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state'] . ' ' . $property['zip_code']); ?>
            </p>
            
            <div class="property-specs">
                <div class="spec-item">
                    <i class="fas fa-bed"></i>
                    <span><?php echo $property['bedrooms']; ?> Bedrooms</span>
                </div>
                <div class="spec-item">
                    <i class="fas fa-bath"></i>
                    <span><?php echo $property['bathrooms']; ?> Bathrooms</span>
                </div>
                <div class="spec-item">
                    <i class="fas fa-ruler-combined"></i>
                    <span><?php echo $property['sqft'] ? $property['sqft'] . ' sqft' : 'Size not specified'; ?></span>
                </div>
                <div class="spec-item">
                    <i class="fas fa-home"></i>
                    <span><?php echo ucfirst($property['property_type']); ?></span>
                </div>
            </div>
            
            <?php if (!empty($property['description'])): ?>
                <div class="property-description">
                    <h3><i class="fas fa-info-circle"></i> Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($amenities)): ?>
                <div class="amenities-section">
                    <h3><i class="fas fa-star"></i> Amenities</h3>
                    <div class="amenities-grid">
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="amenity-item">
                                <i class="fas fa-check"></i> <?php echo ucfirst($amenity); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="property-actions">
                <?php if (isLoggedIn() && isBuyer()): ?>
                    <button class="btn btn-primary btn-book" data-property-id="<?php echo $property['property_id']; ?>">
                        <i class="fas fa-calendar"></i> Book Viewing
                    </button>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <button class="btn btn-favorite <?php echo $isFavorite ? 'active' : ''; ?>" 
                            data-property-id="<?php echo $property['property_id']; ?>">
                        <i class="<?php echo $isFavorite ? 'fas' : 'far'; ?> fa-heart"></i> 
                        <?php echo $isFavorite ? 'Saved' : 'Save to Favorites'; ?>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="seller-info">
                <h3><i class="fas fa-user"></i> Contact Seller</h3>
                <p><strong><?php echo htmlspecialchars($property['seller_name']); ?></strong></p>
                <?php if ($property['seller_email']): ?>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($property['seller_email']); ?></p>
                <?php endif; ?>
                <?php if ($property['seller_phone']): ?>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($property['seller_phone']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    // Similar properties (same city and type), exclude current property
    try {
        $similarStmt = $pdo->prepare("SELECT p.*, 
            (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
            FROM properties p
            WHERE p.status = 'approved' AND p.city = ? AND p.type = ? AND p.property_id <> ?
            ORDER BY p.created_at DESC
            LIMIT 4");
        $similarStmt->execute([$property['city'], $property['type'], $property_id]);
        $similar = $similarStmt->fetchAll();
    } catch (PDOException $e) {
        $similar = [];
    }
    ?>
    <?php if (!empty($similar)): ?>
        <div class="similar-properties">
            <h2 class="section-title"><i class="fas fa-building"></i> Similar Properties</h2>
            <div class="properties-grid">
                <?php foreach ($similar as $sim): ?>
                    <div class="property-card" data-property-id="<?php echo $sim['property_id']; ?>">
                        <?php
                        $primaryImage = $sim['primary_image'] ?? '';
                        if ($primaryImage && strpos($primaryImage, 'assets/') !== 0 && strpos($primaryImage, '/') !== 0) {
                            $primaryImage = 'assets/images/' . ltrim($primaryImage, '/');
                        }
                        ?>
                        <?php if (!empty($primaryImage)): ?>
                            <img src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($sim['title']); ?>" class="property-image">
                        <?php else: ?>
                            <div class="property-image-placeholder">
                                <i class="fas fa-home"></i>
                            </div>
                        <?php endif; ?>
                        <div class="property-info">
                            <h3 class="property-title"><?php echo htmlspecialchars($sim['title']); ?></h3>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($sim['address'] . ', ' . $sim['city']); ?>
                            </p>
                            <div class="property-details">
                                <span><i class="fas fa-bed"></i> <?php echo $sim['bedrooms']; ?> Beds</span>
                                <span><i class="fas fa-bath"></i> <?php echo $sim['bathrooms']; ?> Baths</span>
                                <?php if (!empty($sim['sqft'])): ?>
                                    <span><i class="fas fa-ruler-combined"></i> <?php echo $sim['sqft']; ?> sqft</span>
                                <?php endif; ?>
                            </div>
                            <div class="property-price">
                                <strong>K<?php echo number_format($sim['price']); ?></strong>
                                <span class="property-type-badge"><?php echo ucfirst($sim['type']); ?></span>
                            </div>
                            <div class="property-actions">
                                <a href="property_details.php?id=<?php echo $sim['property_id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-favorite" data-property-id="<?php echo $sim['property_id']; ?>">
                                        <i class="far fa-heart"></i> Save
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Booking Modal -->
<?php if (isLoggedIn() && isBuyer()): ?>
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2><i class="fas fa-calendar"></i> Book Viewing</h2>
        <form id="bookingForm">
            <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
            <div class="form-group">
                <label for="booking_date"><i class="fas fa-calendar"></i> Preferred Date & Time</label>
                <input type="datetime-local" id="booking_date" name="booking_date" required>
            </div>
            <div class="form-group">
                <label for="booking_message"><i class="fas fa-comment"></i> Message (Optional)</label>
                <textarea id="booking_message" name="message" rows="4" placeholder="Add any special requests..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirm Booking</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

