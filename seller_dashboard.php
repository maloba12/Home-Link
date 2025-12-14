<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a seller
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isSeller()) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'Seller Dashboard - HomeLink';
$userId = $_SESSION['user_id'];

// Get seller statistics
$stats = [];

// Total properties
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM properties WHERE seller_id = ?");
$stmt->execute([$userId]);
$stats['total_properties'] = $stmt->fetch()['total'];

// Approved properties
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM properties WHERE seller_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$stats['approved_properties'] = $stmt->fetch()['total'];

// Pending properties
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM properties WHERE seller_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$stats['pending_properties'] = $stmt->fetch()['total'];

// Rejected properties
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM properties WHERE seller_id = ? AND status = 'rejected'");
$stmt->execute([$userId]);
$stats['rejected_properties'] = $stmt->fetch()['total'];

// Total bookings
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings b 
                       JOIN properties p ON b.property_id = p.property_id 
                       WHERE p.seller_id = ?");
$stmt->execute([$userId]);
$stats['total_bookings'] = $stmt->fetch()['total'];

// Total property value
$stmt = $pdo->prepare("SELECT SUM(price) as total FROM properties WHERE seller_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$stats['total_value'] = $stmt->fetch()['total'] ?? 0;

// Get recent properties
$recentStmt = $pdo->prepare("SELECT p.*, 
                             (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image,
                             (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) as booking_count
                             FROM properties p 
                             WHERE p.seller_id = ?
                             ORDER BY p.created_at DESC 
                             LIMIT 5");
$recentStmt->execute([$userId]);
$recentProperties = $recentStmt->fetchAll();

// Get recent bookings
$bookingsStmt = $pdo->prepare("SELECT b.*, p.title as property_title, u.username as buyer_name, u.email as buyer_email
                               FROM bookings b
                               JOIN properties p ON b.property_id = p.property_id
                               JOIN users u ON b.user_id = u.user_id
                               WHERE p.seller_id = ?
                               ORDER BY b.booking_date DESC
                               LIMIT 5");
$bookingsStmt->execute([$userId]);
$recentBookings = $bookingsStmt->fetchAll();

include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-store"></i> Seller Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Manage your properties and bookings.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $stats['total_properties']; ?></h3>
                <p>Total Properties</p>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $stats['approved_properties']; ?></h3>
                <p>Approved</p>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $stats['pending_properties']; ?></h3>
                <p>Pending Approval</p>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $stats['rejected_properties']; ?></h3>
                <p>Rejected</p>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $stats['total_bookings']; ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>

        <div class="stat-card stat-secondary">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h3>K<?php echo number_format($stats['total_value']); ?></h3>
                <p>Total Value</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
        <div class="action-buttons-grid">
            <a href="upload_property.php" class="action-btn btn-primary">
                <i class="fas fa-plus-circle"></i>
                <span>Add New Property</span>
            </a>
            <a href="my_properties.php" class="action-btn btn-success">
                <i class="fas fa-list"></i>
                <span>View All Properties</span>
            </a>
            <a href="my_bookings.php" class="action-btn btn-info">
                <i class="fas fa-calendar"></i>
                <span>Manage Bookings</span>
            </a>
            <a href="profile.php" class="action-btn btn-secondary">
                <i class="fas fa-user-edit"></i>
                <span>Edit Profile</span>
            </a>
        </div>
    </div>

    <!-- Recent Properties -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-building"></i> Recent Properties</h2>
            <a href="my_properties.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        
        <?php if (empty($recentProperties)): ?>
            <div class="empty-state">
                <i class="fas fa-home"></i>
                <p>You haven't listed any properties yet.</p>
                <a href="upload_property.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Your First Property
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Bookings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProperties as $property): ?>
                            <tr>
                                <td>
                                    <div class="property-cell">
                                        <?php if ($property['primary_image']): ?>
                                            <img src="<?php echo htmlspecialchars($property['primary_image']); ?>" alt="Property" class="property-thumb">
                                        <?php else: ?>
                                            <div class="property-thumb-placeholder">
                                                <i class="fas fa-home"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($property['title']); ?></span>
                                    </div>
                                </td>
                                <td><span class="badge"><?php echo ucfirst($property['type']); ?></span></td>
                                <td><strong>K<?php echo number_format($property['price']); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $property['booking_count']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="property_details.php?id=<?php echo $property['property_id']; ?>" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_property.php?id=<?php echo $property['property_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Bookings -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-calendar-check"></i> Recent Bookings</h2>
            <a href="my_bookings.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        
        <?php if (empty($recentBookings)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar"></i>
                <p>No bookings yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Buyer</th>
                            <th>Contact</th>
                            <th>Booking Date</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['property_title']); ?></td>
                                <td><?php echo htmlspecialchars($booking['buyer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['buyer_email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['message'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
