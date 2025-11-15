<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is logged in and is a seller
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit();
}

if (!isSeller()) {
    header('Location: /index.php');
    exit();
}

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
            } else {
                // Use a default image if no images uploaded
                $defaultImages = ['assets/images/image 1.jpeg', 'assets/images/image 2.jpeg', 'assets/images/image 3.jpeg'];
                $randomImage = $defaultImages[array_rand($defaultImages)];
                
                $imageStmt = $pdo->prepare("INSERT INTO images (property_id, image_url, is_primary) VALUES (?, ?, ?)");
                $imageStmt->execute([$propertyId, $randomImage, 1]);
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

$pageTitle = 'Add Property - HomeLink';
include 'seller_header.php';
?>

<div class="admin-layout">
    <?php include 'seller_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-plus-circle"></i> Add New Property</h1>
            <p>List your property for rent or sale</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
                <br>
                <a href="/seller/my_properties.php" class="btn btn-sm btn-primary mt-2">View My Properties</a>
            </div>
        <?php endif; ?>
        
        <div class="admin-section">
            <form method="POST" enctype="multipart/form-data" class="property-form">
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Property Title *</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                            <small>Choose a descriptive title for your property</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="type">Listing Type *</label>
                            <select id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="rent" <?php echo ($_POST['type'] ?? '') === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                                <option value="sale" <?php echo ($_POST['type'] ?? '') === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <small>Describe your property in detail (features, location benefits, etc.)</small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                    
                    <div class="form-group">
                        <label for="address">Address *</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <select id="city" name="city" required>
                                <option value="">Select City</option>
                                <option value="Lusaka" <?php echo ($_POST['city'] ?? '') === 'Lusaka' ? 'selected' : ''; ?>>Lusaka</option>
                                <option value="Livingstone" <?php echo ($_POST['city'] ?? '') === 'Livingstone' ? 'selected' : ''; ?>>Livingstone</option>
                                <option value="Kitwe" <?php echo ($_POST['city'] ?? '') === 'Kitwe' ? 'selected' : ''; ?>>Kitwe</option>
                                <option value="Ndola" <?php echo ($_POST['city'] ?? '') === 'Ndola' ? 'selected' : ''; ?>>Ndola</option>
                                <option value="Kabwe" <?php echo ($_POST['city'] ?? '') === 'Kabwe' ? 'selected' : ''; ?>>Kabwe</option>
                                <option value="Chingola" <?php echo ($_POST['city'] ?? '') === 'Chingola' ? 'selected' : ''; ?>>Chingola</option>
                                <option value="Mufulira" <?php echo ($_POST['city'] ?? '') === 'Mufulira' ? 'selected' : ''; ?>>Mufulira</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="state">Province</label>
                            <select id="state" name="state">
                                <option value="">Select Province</option>
                                <option value="Lusaka Province" <?php echo ($_POST['state'] ?? '') === 'Lusaka Province' ? 'selected' : ''; ?>>Lusaka Province</option>
                                <option value="Southern Province" <?php echo ($_POST['state'] ?? '') === 'Southern Province' ? 'selected' : ''; ?>>Southern Province</option>
                                <option value="Copperbelt Province" <?php echo ($_POST['state'] ?? '') === 'Copperbelt Province' ? 'selected' : ''; ?>>Copperbelt Province</option>
                                <option value="Central Province" <?php echo ($_POST['state'] ?? '') === 'Central Province' ? 'selected' : ''; ?>>Central Province</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="zip_code">Postal Code</label>
                            <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>">
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
                                <option value="apartment" <?php echo ($_POST['property_type'] ?? '') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                <option value="house" <?php echo ($_POST['property_type'] ?? '') === 'house' ? 'selected' : ''; ?>>House</option>
                                <option value="condo" <?php echo ($_POST['property_type'] ?? '') === 'condo' ? 'selected' : ''; ?>>Condo</option>
                                <option value="townhouse" <?php echo ($_POST['property_type'] ?? '') === 'townhouse' ? 'selected' : ''; ?>>Townhouse</option>
                                <option value="studio" <?php echo ($_POST['property_type'] ?? '') === 'studio' ? 'selected' : ''; ?>>Studio</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (ZMW) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                            <small>Enter price in Zambian Kwacha</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bedrooms">Bedrooms</label>
                            <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo htmlspecialchars($_POST['bedrooms'] ?? '0'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="bathrooms">Bathrooms</label>
                            <input type="number" id="bathrooms" name="bathrooms" step="0.5" min="0" value="<?php echo htmlspecialchars($_POST['bathrooms'] ?? '0'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="sqft">Square Feet</label>
                            <input type="number" id="sqft" name="sqft" min="0" value="<?php echo htmlspecialchars($_POST['sqft'] ?? '0'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="amenities">Amenities</label>
                        <div class="amenities-grid">
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Parking" <?php echo in_array('Parking', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>Parking</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Swimming Pool" <?php echo in_array('Swimming Pool', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>Swimming Pool</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Gym" <?php echo in_array('Gym', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>Gym</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Security" <?php echo in_array('Security', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>24/7 Security</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Air Conditioning" <?php echo in_array('Air Conditioning', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>Air Conditioning</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Balcony" <?php echo in_array('Balcony', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>Balcony</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Garden" <?php echo in_array('Garden', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>Garden</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox" name="amenities[]" value="Furnished" <?php echo in_array('Furnished', $_POST['amenities'] ?? []) ? 'checked' : ''; ?>>
                                <span>Furnished</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Property Images</h3>
                    <div class="form-group">
                        <label for="images">Upload Images</label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*">
                        <small>Upload high-quality images. First image will be the primary photo. If no images are uploaded, a default image will be used.</small>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-upload"></i> Upload Property
                    </button>
                    <a href="/seller/my_properties.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
.property-form {
    max-width: 768px;
    margin: 0 auto;
}

.form-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}

.form-section h3 {
    margin-bottom: 20px;
    color: #1f2937;
    font-size: 18px;
}

.form-section h3 i {
    margin-right: 8px;
    color: #3b82f6;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
}

.form-group small {
    display: block;
    margin-top: 4px;
    color: #6b7280;
    font-size: 12px;
}

.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-top: 8px;
}

.amenity-checkbox {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 8px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    transition: background-color 0.2s;
}

.amenity-checkbox:hover {
    background-color: #f3f4f6;
}

.amenity-checkbox input[type="checkbox"] {
    margin-right: 8px;
}

.image-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 12px;
    margin-top: 12px;
}

.image-preview-item {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #d1d5db;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-large {
    padding: 12px 24px;
    font-size: 16px;
}

.alert {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    border: 1px solid;
}

.alert-error {
    background-color: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.alert-success {
    background-color: #f0fdf4;
    border-color: #bbf7d0;
    color: #16a34a;
}

.mt-2 {
    margin-top: 8px;
}
</style>

<script>
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    const files = Array.from(e.target.files);
    files.forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview-item';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Auto-populate province based on city
document.getElementById('city').addEventListener('change', function() {
    const city = this.value;
    const stateSelect = document.getElementById('state');
    
    const cityToProvince = {
        'Lusaka': 'Lusaka Province',
        'Livingstone': 'Southern Province',
        'Kitwe': 'Copperbelt Province',
        'Ndola': 'Copperbelt Province',
        'Kabwe': 'Central Province',
        'Chingola': 'Copperbelt Province',
        'Mufulira': 'Copperbelt Province'
    };
    
    if (cityToProvince[city]) {
        stateSelect.value = cityToProvince[city];
    }
});
</script>

<?php include '../includes/footer.php'; ?>
