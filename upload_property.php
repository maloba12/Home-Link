<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
requireSeller();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $zipCode = sanitizeInput($_POST['zip_code'] ?? '');
    $price = $_POST['price'] ?? 0;
    $type = $_POST['type'] ?? '';
    $propertyType = $_POST['property_type'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? 0;
    $bathrooms = $_POST['bathrooms'] ?? 0;
    $sqft = $_POST['sqft'] ?? 0;
    $amenities = $_POST['amenities'] ?? [];
    
    // Validation
    if (empty($title) || empty($address) || empty($city) || empty($price) || empty($type) || empty($propertyType)) {
        $error = 'Please fill in all required fields!';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0!';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Insert property
            $stmt = $pdo->prepare("INSERT INTO properties (seller_id, title, description, address, city, state, zip_code, price, type, property_type, bedrooms, bathrooms, sqft) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([getUserId(), $title, $description, $address, $city, $state, $zipCode, $price, $type, $propertyType, $bedrooms, $bathrooms, $sqft]);
            
            $propertyId = $pdo->lastInsertId();
            
            // Insert amenities
            if (!empty($amenities) && is_array($amenities)) {
                $amenityStmt = $pdo->prepare("INSERT INTO amenities (property_id, amenity) VALUES (?, ?)");
                foreach ($amenities as $amenity) {
                    if (!empty(trim($amenity))) {
                        $amenityStmt->execute([$propertyId, trim($amenity)]);
                    }
                }
            }
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = 'assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $imageStmt = $pdo->prepare("INSERT INTO images (property_id, image_url, is_primary) VALUES (?, ?, ?)");
                
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        $newName = uniqid() . '_' . basename($name);
                        $uploadPath = $uploadDir . $newName;
                        
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $isPrimary = ($key === 0) ? 1 : 0;
                            $imageStmt->execute([$propertyId, $uploadPath, $isPrimary]);
                        }
                    }
                }
            }
            
            $pdo->commit();
            $success = 'Property uploaded successfully! It will be reviewed by admin before being published.';
            
            // Clear form
            $_POST = [];
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Upload failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Upload Property - HomeLink';
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2><i class="fas fa-plus-circle"></i> Upload New Property</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="property-form">
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Property Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="rent">Rent</option>
                            <option value="sale">Sale</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                
                <div class="form-group">
                    <label for="address">Address *</label>
                    <input type="text" id="address" name="address" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state">
                    </div>
                    
                    <div class="form-group">
                        <label for="zip_code">Zip Code</label>
                        <input type="text" id="zip_code" name="zip_code">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3><i class="fas fa-tags"></i> Property Details</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="property_type">Property Type *</label>
                        <select id="property_type" name="property_type" required>
                            <option value="">Select Type</option>
                            <option value="apartment">Apartment</option>
                            <option value="house">House</option>
                            <option value="condo">Condo</option>
                            <option value="townhouse">Townhouse</option>
                            <option value="studio">Studio</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bedrooms">Bedrooms</label>
                        <input type="number" id="bedrooms" name="bedrooms" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="bathrooms">Bathrooms</label>
                        <input type="number" id="bathrooms" name="bathrooms" step="0.1" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="sqft">Square Feet</label>
                        <input type="number" id="sqft" name="sqft" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="amenities">Amenities (comma-separated)</label>
                    <input type="text" id="amenities" name="amenities" placeholder="e.g., parking, gym, pool">
                </div>
            </div>
            
            <div class="form-section">
                <h3><i class="fas fa-images"></i> Images</h3>
                <div class="form-group">
                    <label for="images">Upload Images</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*">
                    <small>You can upload multiple images. The first image will be the primary image.</small>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-upload"></i> Upload Property
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

