<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
requireLogin();

$error = '';
$success = '';

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([getUserId()]);
$user = $stmt->fetch();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (!empty($email) && !validateEmail($email)) {
        $error = 'Invalid email format!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
            $stmt->execute([$fullName, $email, $phone, getUserId()]);
            $success = 'Profile updated successfully!';
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([getUserId()]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Update failed: ' . $e->getMessage();
        }
    }
}

// Get user's favorites
try {
    $favStmt = $pdo->prepare("SELECT p.*, 
                              (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
                              FROM properties p
                              INNER JOIN favorites f ON p.property_id = f.property_id
                              WHERE f.user_id = ?
                              ORDER BY f.created_at DESC");
    $favStmt->execute([getUserId()]);
    $favorites = $favStmt->fetchAll();
} catch (PDOException $e) {
    $favorites = [];
}

// Get user's bookings
try {
    $bookingStmt = $pdo->prepare("SELECT b.*, p.title, p.address, p.city
                                   FROM bookings b
                                   INNER JOIN properties p ON b.property_id = p.property_id
                                   WHERE b.user_id = ?
                                   ORDER BY b.created_at DESC");
    $bookingStmt->execute([getUserId()]);
    $bookings = $bookingStmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}

$pageTitle = 'My Profile - HomeLink';
include 'includes/header.php';
?>

<div class="container">
    <div class="profile-tabs">
        <button class="tab-button active" onclick="showTab('profile')">
            <i class="fas fa-user"></i> Profile
        </button>
        <button class="tab-button" onclick="showTab('favorites')">
            <i class="fas fa-heart"></i> Favorites
        </button>
        <button class="tab-button" onclick="showTab('bookings')">
            <i class="fas fa-calendar"></i> Bookings
        </button>
    </div>
    
    <!-- Profile Tab -->
    <div id="profileTab" class="tab-content active">
        <div class="form-container">
            <h2><i class="fas fa-user"></i> My Profile</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="profile-form">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small>Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" disabled>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
    </div>
    
    <!-- Favorites Tab -->
    <div id="favoritesTab" class="tab-content">
        <h2><i class="fas fa-heart"></i> Saved Properties</h2>
        
        <?php if (empty($favorites)): ?>
            <div class="no-results">
                <i class="far fa-heart"></i>
                <p>You haven't saved any properties yet.</p>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($favorites as $property): ?>
                    <div class="property-card" data-property-id="<?php echo $property['property_id']; ?>">
                        <?php if ($property['primary_image']): ?>
                            <img src="<?php echo htmlspecialchars($property['primary_image']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
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
                                <strong>$<?php echo number_format($property['price']); ?></strong>
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
    
    <!-- Bookings Tab -->
    <div id="bookingsTab" class="tab-content">
        <h2><i class="fas fa-calendar"></i> My Bookings</h2>
        
        <?php if (empty($bookings)): ?>
            <div class="no-results">
                <i class="fas fa-calendar-times"></i>
                <p>You don't have any bookings yet.</p>
            </div>
        <?php else: ?>
            <div class="bookings-list">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-info">
                            <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['address'] . ', ' . $booking['city']); ?></p>
                            <p><i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', strtotime($booking['booking_date'])); ?></p>
                            <?php if ($booking['message']): ?>
                                <p><i class="fas fa-comment"></i> <?php echo htmlspecialchars($booking['message']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="booking-status">
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tab + 'Tab').classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>

